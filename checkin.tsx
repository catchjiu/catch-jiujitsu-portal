import React from 'react';
import ReactDOM from 'react-dom/client';
import { CheckInApp } from './pages/checkin/CheckInApp';

const rootElement = document.getElementById('checkin-root');
if (!rootElement) {
  throw new Error('Check-in root element #checkin-root not found');
}

const root = ReactDOM.createRoot(rootElement);
root.render(
  <React.StrictMode>
    <CheckInApp />
  </React.StrictMode>
);
