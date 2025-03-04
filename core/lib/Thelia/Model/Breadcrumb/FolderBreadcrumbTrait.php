<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thelia\Model\Breadcrumb;

use Symfony\Component\Routing\Router;
use Thelia\Core\Translation\Translator;
use Thelia\Model\FolderQuery;
use Thelia\Tools\URL;

trait FolderBreadcrumbTrait
{
    public function getBaseBreadcrumb(Router $router, $folderId)
    {
        $translator = Translator::getInstance();
        $foldersUrl = $router->generate('admin.folders.default', [], Router::ABSOLUTE_URL);
        $breadcrumb = [
            $translator->trans('Home') => URL::getInstance()->absoluteUrl('/admin'),
            $translator->trans('Folder') => $foldersUrl,
        ];

        $depth = 20;
        $ids = [];
        $results = [];

        // Todo refactor this ugly code
        do {
            $folder = FolderQuery::create()
                ->filterById($folderId)
                ->findOne();

            if ($folder != null) {
                $results[] = [
                    'ID' => $folder->getId(),
                    'TITLE' => $folder->getTitle(),
                    'URL' => $folder->getUrl(),
                ];

                $currentId = $folder->getParent();

                if ($currentId > 0) {
                    // Prevent circular refererences
                    if (\in_array($currentId, $ids)) {
                        throw new \LogicException(
                            sprintf(
                                'Circular reference detected in folder ID=%d hierarchy (folder ID=%d appears more than one times in path)',
                                $folderId,
                                $currentId
                            )
                        );
                    }

                    $ids[] = $currentId;
                }
            }
        } while ($folder != null && $currentId > 0 && --$depth > 0);

        foreach ($results as $result) {
            $breadcrumb[$result['TITLE']] = sprintf(
                '%s?parent=%d',
                $router->generate(
                    'admin.folders.default',
                    [],
                    Router::ABSOLUTE_URL
                ),
                $result['ID']
            );
        }

        return $breadcrumb;
    }

    public function getFolderBreadcrumb(Router $router, $tab, $locale)
    {
        if (!method_exists($this, 'getFolder')) {
            return null;
        }

        /** @var \Thelia\Model\Folder $folder */
        $folder = $this->getFolder();
        $breadcrumb = $this->getBaseBreadcrumb($router, $this->getParentId());

        $folder->setLocale($locale);

        $breadcrumb[$folder->getTitle()] = sprintf(
            '%s?current_tab=%s',
            $router->generate(
                'admin.folders.update',
                ['folder_id' => $folder->getId()],
                Router::ABSOLUTE_URL
            ),
            $tab
        );

        return $breadcrumb;
    }

    public function getContentBreadcrumb(Router $router, $tab, $locale)
    {
        if (!method_exists($this, 'getContent')) {
            return null;
        }

        /** @var \Thelia\Model\Content $content */
        $content = $this->getContent();

        $breadcrumb = $this->getBaseBreadcrumb($router, $content->getDefaultFolderId());

        $content->setLocale($locale);

        $breadcrumb[$content->getTitle()] = sprintf(
            '%s?current_tab=%s',
            $router->generate(
                'admin.content.update',
                ['content_id' => $content->getId()],
                Router::ABSOLUTE_URL
            ),
            $tab
        );

        return $breadcrumb;
    }
}
