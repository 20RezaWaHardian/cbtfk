import './bootstrap';
import { createInertiaApp } from '@inertiajs/inertia-react';
import React from 'react';
// import ReactDOM from 'react-dom'; // Import ReactDOM correctly
import { createRoot } from 'react-dom/client';

createInertiaApp({
    resolve: name => {
        return import(`./Pages/${name}`).then(module => module.default || module);
    },
    setup({ el, App, props }) {
        const root = createRoot(el); // React 18
        root.render(<App {...props} />);
    }
})
