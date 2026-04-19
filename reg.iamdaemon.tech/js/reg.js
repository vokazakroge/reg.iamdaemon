const form = document.getElementById('regForm');
const submitBtn = document.getElementById('submitBtn');
const usernameInput = document.getElementById('username');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const subdomainPreview = document.getElementById('subdomainPreview');
const formView = document.getElementById('formView');
const successView = document.getElementById('successView');
const userLink = document.getElementById('userLink');
const msgs = {
    username: document.getElementById('usernameMsg'),
    email: document.getElementById('emailMsg'),
    password: document.getElementById('passwordMsg')
};

let checkTimer;
let isUsernameAvailable = false;

const validateUsername = (v) => {
    if (!v) return { valid: false, msg: '' };
    if (!/^[a-z0-9-]{3,20}$/.test(v)) return { valid: false, msg: 'tolko a-z, 0-9 i defis, 3-20 simvolov' };
    return { valid: true, msg: '' };
};

const validateEmail = (v) => {
    if (!v) return { valid: false, msg: '' };
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!re.test(v)) return { valid: false, msg: 'nekorrektnyy format email' };
    return { valid: true, msg: 'OK' };
};

const validatePassword = (v) => {
    if (!v) return { valid: false, msg: '' };
    if (v.length < 8) return { valid: false, msg: 'minimum 8 simvolov' };
    return { valid: true, msg: 'OK' };
};

function setFieldState(input, msgEl, state) {
    input.classList.remove('valid', 'invalid');
    msgEl.classList.remove('error', 'success', 'checking');
    msgEl.textContent = state.msg;
    if (state.msg) {
        if (state.valid) {
            input.classList.add('valid');
            msgEl.classList.add('success');
        } else {
            input.classList.add('invalid');
            msgEl.classList.add('error');
        }
    }
    checkFormReady();
}

function checkFormReady() {
    const u = validateUsername(usernameInput.value);
    const e = validateEmail(emailInput.value);
    const p = validatePassword(passwordInput.value);
    submitBtn.disabled = !(u.valid && isUsernameAvailable && e.valid && p.valid);
}

usernameInput.addEventListener('input', (e) => {
    let val = e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
    e.target.value = val;
    subdomainPreview.textContent = val ? val + '.iamdaemon.tech' : 'imya.iamdaemon.tech';
    subdomainPreview.style.opacity = val ? '1' : '0.6';

    const format = validateUsername(val);
    if (!format.valid) {
        isUsernameAvailable = false;
        setFieldState(e.target, msgs.username, format);
        return;
    }

    msgs.username.textContent = 'proveryayem...';
    msgs.username.classList.add('checking');
    msgs.username.classList.remove('error', 'success');
    usernameInput.classList.remove('valid', 'invalid');
    isUsernameAvailable = false;
    checkFormReady();

    clearTimeout(checkTimer);
    checkTimer = setTimeout(async() => {
        try {
            const res = await fetch('/api/check_username.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: val })
            });
            const data = await res.json();
            console.log('Username check:', data);

            if (data.available === true) {
                isUsernameAvailable = true;
                setFieldState(e.target, msgs.username, { valid: true, msg: 'dostupno' });
            } else {
                isUsernameAvailable = false;
                setFieldState(e.target, msgs.username, { valid: false, msg: 'uzhe zanyato' });
            }
        } catch (err) {
            console.error('Check error:', err);
            isUsernameAvailable = false;
            setFieldState(e.target, msgs.username, { valid: false, msg: 'oshibka seti' });
        }
    }, 600);
});

emailInput.addEventListener('input', (e) => {
    setFieldState(e.target, msgs.email, validateEmail(e.target.value.trim()));
});

passwordInput.addEventListener('input', (e) => {
    setFieldState(e.target, msgs.password, validatePassword(e.target.value));
});

form.addEventListener('submit', async(e) => {
    e.preventDefault();

    const username = usernameInput.value.trim();
    const email = emailInput.value.trim();
    const password = passwordInput.value;

    submitBtn.disabled = true;
    submitBtn.textContent = 'sozdayom...';

    try {
        const res = await fetch('/api/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, email, password })
        });

        const data = await res.json();

        if (!res.ok || data.errors) {
            throw new Error(data.errors ? data.errors.join('\n') : data.message || 'Oshibka servera');
        }

        const finalUser = data.username || username;
        formView.style.display = 'none';
        successView.style.display = 'block';
        userLink.href = 'https://' + finalUser + '.iamdaemon.tech';
        userLink.textContent = finalUser + '.iamdaemon.tech';

        document.getElementById('dashboardBtn').addEventListener('click', () => {
            sessionStorage.setItem('daemon_user', finalUser);
            window.location.href = '/dashboard';
        });

    } catch (err) {
        msgs.username.textContent = err.message;
        msgs.username.classList.add('error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'sozdat akkaunt';
    }
});