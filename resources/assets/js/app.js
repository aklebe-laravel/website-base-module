// pusher
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Only vars with prefix "VITE_" will work for import.meta.env!
if (import.meta.env.VITE_PUSHER_APP_KEY) {
    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        forceTLS: true
    });
} else {
    console.log('VITE_PUSHER_APP_KEY is required. No Echo will be instantiated!')
}

import CliConsole from './cli-console';
window.CliConsole = CliConsole;

const cliConsoleEvent = new Event("cli-console-ready");
document.dispatchEvent(cliConsoleEvent);
