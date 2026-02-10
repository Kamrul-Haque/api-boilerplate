<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoutePay Integration Test</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 40px; background: #f0f2f5; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
        h2 { margin-top: 0; color: #333; font-size: 1.2rem; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #666; font-size: 0.9rem; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #4f46e5; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; transition: background 0.2s; }
        button:hover { background: #4338ca; }
        button:disabled { background: #ccc; cursor: not-allowed; }
        .divider { height: 1px; background: #eee; margin: 25px 0; }
        #log { background: #1e1e1e; color: #00ff00; padding: 15px; border-radius: 4px; margin-top: 20px; font-family: monospace; font-size: 0.85rem; white-space: pre-wrap; max-height: 200px; overflow-y: auto; }
    </style>
</head>
<body>
<div class="card">
    <h2>1. Login (Get Token)</h2>
    <div class="form-group">
        <label>Email</label>
        <input type="email" id="email" value="parent@example.com">
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" id="password" value="password">
    </div>
    <button onclick="login()">Login</button>
    <div class="divider"></div>
    <h2>2. Initiate Payment</h2>
    <div class="form-group">
        <label>Event ID</label>
        <input type="number" id="event_id" value="1">
    </div>
    <div class="form-group">
        <label>Student ID</label>
        <input type="number" id="student_id" value="1">
    </div>
    <button onclick="initiatePayment()" id="payBtn" disabled>Pay Now</button>
    <div id="log">Logs will appear here...</div>
</div>
<script>
    // Use Laravel's URL helper to get the base URL
    const API_BASE = "{{ url('/api') }}"; 
    let authToken = '';
    function log(msg) {
        const logDiv = document.getElementById('log');
        logDiv.innerText += `> ${msg}\n`;
        logDiv.scrollTop = logDiv.scrollHeight;
        console.log(msg);
    }
    async function login() {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        try {
            log('Logging in...');
            const res = await fetch(`${API_BASE}/login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const data = await res.json();
            
            // Adjust this check based on your actual login response (e.g., data.access_token)
            if (data.token || data.access_token) { 
                authToken = data.token || data.access_token;
                log('Login Successful!');
                document.getElementById('payBtn').disabled = false;
                document.getElementById('payBtn').innerText = 'Pay Now (Ready)';
            } else {
                log('Login Failed: ' + JSON.stringify(data));
            }
        } catch (e) {
            log('Error: ' + e.message);
        }
    }
    async function initiatePayment() {
        if (!authToken) return alert('Please login first');
        const event_id = document.getElementById('event_id').value;
        const student_id = document.getElementById('student_id').value;
        try {
            log('Initiating Payment...');
            const res = await fetch(`${API_BASE}/routepay/initiate-payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authToken}`
                },
                body: JSON.stringify({ event_id, student_id })
            });
            const response = await res.json();
            
            if (response.status === 'success') {
                log('Payment Initiated. Redirecting...');
                redirectToRoutePay(response.data);
            } else {
                log('Payment Failed: ' + (response.message || JSON.stringify(response)));
            }
        } catch (e) {
            log('Error: ' + e.message);
        }
    }
    function redirectToRoutePay(paymentData) {
        // 1. Create a form dynamically
        const form = document.createElement('form');
        form.method = paymentData.method; 
        form.action = paymentData.url;    
        
        // 2. Add fields from "required_field"
        const fields = paymentData.required_field;
        for (const key in fields) {
            if (fields.hasOwnProperty(key)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }
        }
        // 3. Append to body and submit
        document.body.appendChild(form);
        form.submit();
    }
</script>
</body>
</html>