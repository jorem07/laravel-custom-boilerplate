<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reverb Test</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 2rem; }
        #status { color: #666; margin-bottom: 1rem; }
        #message { white-space: pre-wrap; background: #f4f4f5; padding: 1rem; border-radius: 8px; }
    </style>
</head>
<body>
    <h1>Reverb live test</h1>
    <p id="status">Connecting to Reverb...</p>
    <pre id="message">Waiting for broadcast...</pre>

    @vite('resources/js/app.js')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const status = document.getElementById('status');
            const message = document.getElementById('message');

            const channel = window.Echo.channel('test-channel');

            channel.subscribed(() => {
                status.textContent = 'Connected to test-channel. Update a user via API to see live data.';
            });

            channel.error((error) => {
                status.textContent = 'Channel error. Check Reverb server and VITE_REVERB_* env vars.';
                console.error(error);
            });

            channel.listen('.test-channel.sent', (event) => {
                console.log('Broadcast received:', event);
                status.textContent = 'Last update: ' + new Date().toLocaleTimeString();
                message.textContent = JSON.stringify(event.body ?? event, null, 2);
            });
        });
    </script>
</body>
</html>
