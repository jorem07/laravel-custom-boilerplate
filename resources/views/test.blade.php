<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Queue Live Test</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 2rem; max-width: 1100px; }
        #status { color: #666; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #e4e4e7; padding: 0.5rem 0.75rem; text-align: left; }
        th { background: #f4f4f5; }
        .panel { background: #f4f4f5; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        label { display: block; margin-bottom: 0.25rem; font-size: 0.875rem; }
        input { padding: 0.5rem; margin-bottom: 0.75rem; width: 100%; max-width: 240px; }
        button { padding: 0.5rem 1rem; cursor: pointer; margin-right: 0.5rem; }
        #called { margin-top: 1rem; padding: 0.75rem; background: #ecfdf5; border-radius: 8px; }
        .empty { color: #71717a; font-style: italic; }
        .logged-in { color: #047857; font-weight: 600; }
        .logged-out { color: #71717a; }
        h2 { margin-top: 0; font-size: 1.1rem; }
    </style>
</head>
<body>
    <h1>Queue live test</h1>
    <p id="status">Loading...</p>

    <div class="grid">
        <div class="panel">
            <h2>Counter login</h2>
            <label for="login_counter_id">Counter ID</label>
            <input id="login_counter_id" type="number" value="1" min="1">
            <label for="login_user_id">User ID</label>
            <input id="login_user_id" type="number" value="1" min="1">
            <button id="counter-login" type="button">Log in to counter</button>
            <button id="counter-logout" type="button">Log out from counter</button>
        </div>

        <div class="panel">
            <h2>Call next</h2>
            <label for="counter_id">Counter ID</label>
            <input id="counter_id" type="number" value="1" min="1">
            <label for="user_id">User ID (optional if logged in)</label>
            <input id="user_id" type="number" placeholder="Uses logged-in user">
            <button id="call-next" type="button">Call next queue</button>
        </div>
    </div>

    <div id="called"></div>

    <h2>Counters</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Service</th>
                <th>Logged-in user</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="counter-body">
            <tr><td colspan="5" class="empty">No counters yet.</td></tr>
        </tbody>
    </table>

    <h2>Counter login logs (today)</h2>
    <table>
        <thead>
            <tr>
                <th>Counter</th>
                <th>User</th>
                <th>Log in</th>
                <th>Log out</th>
            </tr>
        </thead>
        <tbody id="log-body">
            <tr><td colspan="4" class="empty">No login logs yet.</td></tr>
        </tbody>
    </table>

    <h2>Queue</h2>
    <table>
        <thead>
            <tr>
                <th>Queue No</th>
                <th>Status</th>
                <th>Counter</th>
                <th>User</th>
                <th>Started</th>
            </tr>
        </thead>
        <tbody id="queue-body">
            <tr><td colspan="5" class="empty">No queue entries yet.</td></tr>
        </tbody>
    </table>

    @vite('resources/js/app.js')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const status = document.getElementById('status');
            const queueBody = document.getElementById('queue-body');
            const counterBody = document.getElementById('counter-body');
            const logBody = document.getElementById('log-body');
            const called = document.getElementById('called');
            const apiBase = '/api-queuing';

            const renderQueues = (queues) => {
                if (!queues || queues.length === 0) {
                    queueBody.innerHTML = '<tr><td colspan="5" class="empty">No queue entries yet.</td></tr>';
                    return;
                }

                queueBody.innerHTML = queues.map((queue) => `
                    <tr>
                        <td>${queue.queue_no}</td>
                        <td>${queue.queue_statuses_name}</td>
                        <td>${queue.counter_name ?? queue.counter_id ?? '—'}</td>
                        <td>${queue.user_name ?? queue.user_id ?? '—'}</td>
                        <td>${queue.time_start ?? '—'}</td>
                    </tr>
                `).join('');
            };

            const renderCounters = (counters) => {
                if (!counters || counters.length === 0) {
                    counterBody.innerHTML = '<tr><td colspan="5" class="empty">No counters yet.</td></tr>';
                    return;
                }

                counterBody.innerHTML = counters.map((counter) => `
                    <tr>
                        <td>${counter.id}</td>
                        <td>${counter.name}</td>
                        <td>${counter.office_service_name ?? counter.office_service_id ?? '—'}</td>
                        <td>${counter.user_name ?? '—'}</td>
                        <td class="${counter.is_logged_in ? 'logged-in' : 'logged-out'}">
                            ${counter.is_logged_in ? 'Logged in' : 'Available'}
                        </td>
                    </tr>
                `).join('');
            };

            const renderLogs = (logs) => {
                if (!logs || logs.length === 0) {
                    logBody.innerHTML = '<tr><td colspan="4" class="empty">No login logs yet.</td></tr>';
                    return;
                }

                logBody.innerHTML = logs.map((log) => `
                    <tr>
                        <td>${log.counter_name ?? log.counter_id ?? '—'}</td>
                        <td>${log.user_name ?? log.user_id ?? '—'}</td>
                        <td>${log.log_in ?? '—'}</td>
                        <td>${log.log_out ?? '—'}</td>
                    </tr>
                `).join('');
            };

            const renderCalled = (calledQueue, message) => {
                if (!calledQueue) {
                    called.innerHTML = `<strong>${message ?? 'No queue was called.'}</strong>`;
                    return;
                }

                called.innerHTML = `
                    <strong>${message ?? 'Queue called'}</strong><br>
                    ${calledQueue.queue_no} → Counter: ${calledQueue.counter_name ?? calledQueue.counter_id},
                    User: ${calledQueue.user_name ?? calledQueue.user_id}
                `;
            };

            const applyPayload = (payload) => {
                status.textContent = `${payload.message ?? 'Updated'} — ${new Date().toLocaleTimeString()}`;

                if (payload.action === 'counter_login' || payload.action === 'counter_logout') {
                    renderCounters(payload.body ?? []);
                    renderLogs(payload.counter_logs ?? []);
                    return;
                }

                renderQueues(payload.body ?? []);
                renderCounters(payload.counters ?? []);
                renderLogs(payload.counter_logs ?? []);

                if (payload.action === 'next') {
                    renderCalled(payload.called ?? null, payload.message);
                }
            };

            const loadInitialData = async () => {
                try {
                    const [queueRes, counterRes, logRes] = await Promise.all([
                        fetch(`${apiBase}/queues/current`),
                        fetch(`${apiBase}/counters/active`),
                        fetch(`${apiBase}/counter-user-logs`),
                    ]);

                    const queueData = await queueRes.json();
                    const counterData = await counterRes.json();
                    const logData = await logRes.json();

                    applyPayload({
                        message: 'Initial data loaded.',
                        action: 'list',
                        body: queueData.body ?? [],
                        counters: queueData.others?.counters ?? counterData.body ?? [],
                        counter_logs: queueData.others?.counter_logs ?? logData.body ?? [],
                    });
                } catch (error) {
                    status.textContent = 'Failed to load initial data.';
                    console.error(error);
                }
            };

            window.Echo.channel('queue-channel')
                .subscribed(() => {
                    status.textContent = 'Connected to queue-channel. Loading data...';
                    loadInitialData();
                })
                .error((error) => {
                    status.textContent = 'Channel error. Check Reverb server and VITE_REVERB_* env vars.';
                    console.error(error);
                })
                .listen('.queue.updated', (event) => {
                    console.log('Broadcast received:', event);
                    applyPayload(event);
                });

            const postJson = async (url, body = {}) => {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(body),
                });

                const data = await response.json();

                if (!response.ok) {
                    status.textContent = data.message ?? 'Request failed.';
                    return null;
                }

                return data;
            };

            document.getElementById('counter-login').addEventListener('click', async () => {
                const data = await postJson(`${apiBase}/counters/login`, {
                    counter_id: Number(document.getElementById('login_counter_id').value),
                    user_id: Number(document.getElementById('login_user_id').value),
                });

                if (!data) return;

                applyPayload({
                    message: data.message,
                    action: 'counter_login',
                    body: data.others?.counters ?? data.body ?? [],
                    counter_logs: data.others?.counter_logs ?? [],
                });
            });

            document.getElementById('counter-logout').addEventListener('click', async () => {
                const data = await postJson(`${apiBase}/counters/logout`, {
                    counter_id: Number(document.getElementById('login_counter_id').value),
                });

                if (!data) return;

                applyPayload({
                    message: data.message,
                    action: 'counter_logout',
                    body: data.others?.counters ?? data.body ?? [],
                    counter_logs: data.others?.counter_logs ?? [],
                });
            });

            document.getElementById('call-next').addEventListener('click', async () => {
                const counterId = document.getElementById('counter_id').value;
                const userId = document.getElementById('user_id').value;
                const payload = { counter_id: Number(counterId) };

                if (userId) {
                    payload.user_id = Number(userId);
                }

                const data = await postJson(`${apiBase}/queues/next`, payload);
                if (!data) return;

                applyPayload({
                    message: data.message,
                    body: data.body ?? [],
                    called: data.others?.called ?? null,
                    counters: data.others?.counters ?? [],
                    counter_logs: data.others?.counter_logs ?? [],
                    action: 'next',
                });
            });
        });
    </script>
</body>
</html>
