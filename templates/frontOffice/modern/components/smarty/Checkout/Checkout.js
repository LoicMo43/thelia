import messages, { locale } from '@components/React/intl';

import Checkout from '@components/React/Checkout';
import { IntlProvider } from 'react-intl';
import { Provider } from 'react-redux';
import { QueryClientProvider } from 'react-query';
import React from 'react';
import { queryClient } from '@openstudio/thelia-api-utils';
import { createRoot } from 'react-dom/client';
import store from '@redux/store';
// import Loader from '@components/React/Loader';

function CheckoutWrapper() {
  return (
    <QueryClientProvider client={queryClient}>
      <IntlProvider locale={locale} messages={messages[locale]}>
        <Provider store={store}>
          <Checkout />
        </Provider>
      </IntlProvider>
    </QueryClientProvider>
  );
}

export default function CheckoutPage() {
  const DOMElement = document.getElementById('Checkout');

  if (!DOMElement) return;

  const root = createRoot(DOMElement);

  root.render(<CheckoutWrapper />);
}
