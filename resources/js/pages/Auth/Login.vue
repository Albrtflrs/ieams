<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
}

const imageUrl = (path) => {
    return `${window.location.origin}${path}`;
};

function ripple(e) {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;

    const button = e.currentTarget;
    const rect = button.getBoundingClientRect();
    const diameter = Math.max(rect.width, rect.height);
    const radius = diameter / 2;

    const circle = document.createElement('span');
    circle.className = 'ripple-circle';
    circle.style.width = circle.style.height = `${diameter}px`;
    circle.style.left = `${e.clientX - rect.left - radius}px`;
    circle.style.top = `${e.clientY - rect.top - radius}px`;

    const existing = button.querySelector('.ripple-circle');
    if (existing) existing.remove();

    button.appendChild(circle);
    circle.addEventListener('animationend', () => circle.remove());
}

const frostFlecks = Array.from({ length: 18 }, (_, i) => {
    const size = Math.random() * 3 + 1.5;
    const left = Math.random() * 100;
    const duration = Math.random() * 10 + 16;
    const delay = Math.random() * -26;
    const driftX = (Math.random() - 0.5) * 60;

    let color;
    const roll = i % 5;
    if (roll === 0) color = 'rgba(212, 162, 76, 0.6)';
    else if (roll === 1) color = 'rgba(61, 170, 134, 0.6)';
    else color = 'rgba(255, 255, 255, 0.75)';

    return {
        id: i,
        style: {
            left: `${left}%`,
            width: `${size}px`,
            height: `${size}px`,
            animationDuration: `${duration}s`,
            animationDelay: `${delay}s`,
            '--drift-x': `${driftX}px`,
            '--fleck-color': color,
        },
    };
});
</script>

<template>
    <div class="auth-container">

        <!-- ─── Login splash overlay ─── -->
        <Transition name="splash">
            <div v-if="form.processing" class="splash-overlay">
                <div class="splash-card">
                    <img :src="imageUrl('/images/logow.png')" alt="IEAMS" class="splash-logo" />
                    <p class="splash-text">Signing in…</p>
                    <div class="splash-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Full‑page background image -->
        <div
            class="bg-wallpaper"
            :style="{ backgroundImage: `url(${imageUrl('/images/calc1.jpg')})` }"
            aria-hidden="true"
        ></div>

        <!-- Decorative background -->
        <div class="auth-bg" aria-hidden="true">
            <span class="blob blob--gold"></span>
            <span class="blob blob--teal"></span>
            <div class="frost-field">
                <span
                    v-for="f in frostFlecks"
                    :key="f.id"
                    class="frost-fleck"
                    :style="f.style"
                ></span>
            </div>
        </div>

        <!-- ─── Login Card with Entrance Animation ─── -->
        <Transition appear name="login">
            <div class="auth-grid">
                <span class="grain" aria-hidden="true"></span>

                <!-- Brand panel (left) -->
                <div class="brand-panel">
                    <img :src="imageUrl('/images/logow.png')" alt="" class="brand-watermark" aria-hidden="true" />

                    <div class="brand-content">
                        <h1 class="brand-title">IEAMS</h1>
                        <p class="brand-subtitle">IT Expert Accounting Management System</p>
                        <div class="brand-divider"></div>
                        <p class="brand-tagline">
                            Streamline your financial workflow with precision and clarity.
                        </p>
                    </div>
                </div>

                <!-- Form panel (right) -->
                <div class="form-panel">
                    <div class="form-card">
                        <div class="form-header">
                            <h2>Welcome back</h2>
                            <p>Sign in to your account to continue.</p>
                        </div>

                        <form @submit.prevent="submit" class="auth-form">
                            <div class="field field--stagger">
                                <label for="email">Email address</label>
                                <div class="field__control">
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        autofocus
                                        autocomplete="email"
                                        placeholder="you@ieams.com"
                                        :class="{ 'has-error': form.errors.email }"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.email" class="error">{{ form.errors.email }}</p>
                            </div>

                            <div class="field field--stagger" style="animation-delay: 0.1s">
                                <label for="password">Password</label>
                                <div class="field__control">
                                    <input
                                        id="password"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                        :class="{ 'has-error': form.errors.password }"
                                        required
                                    />
                                    <button
                                        type="button"
                                        class="toggle-visibility"
                                        @click="showPassword = !showPassword"
                                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                    >
                                        <svg v-if="showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L9.88 9.88"/>
                                        </svg>
                                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="error">{{ form.errors.password }}</p>
                            </div>

                            <div class="field field--inline field--stagger" style="animation-delay: 0.2s">
                                <label class="checkbox">
                                    <input type="checkbox" v-model="form.remember" />
                                    <span>Remember me</span>
                                </label>
                            </div>

                            <button
                                type="submit"
                                class="submit-btn field--stagger"
                                style="animation-delay: 0.3s"
                                :disabled="form.processing"
                                @mousedown="ripple"
                            >
                                <span class="btn-content">
                                    <svg v-if="form.processing" class="spinner" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25" />
                                        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                                    </svg>
                                    <span>{{ form.processing ? 'Signing in…' : 'Sign in' }}</span>
                                </span>
                            </button>
                        </form>

                        <div class="form-footer">
                            <span>IEAMS v1.0.0</span>
                            <span class="footer-separator">·</span>
                            <span>Authorized access only</span>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.auth-container {
    --gold: #d4a24c;
    --teal: #3daa86;
    --dark: #0e1a18;
    --text-light: #f0ede8;
    --text-muted: #a8a49c;
    --error: #d47a7a;

    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    font-family: 'Inter', system-ui, sans-serif;
    color: var(--text-light);
    position: relative;
    overflow: hidden;
    background: radial-gradient(ellipse at 10% 20%, #142a24, #0b1412);
}

/* ─── Splash Overlay ─── */
.splash-overlay {
    position: fixed;
    inset: 0;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(ellipse at 10% 20%, #142a24, #0b1412);
}

.splash-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.25rem;
    animation: splash-pop 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

.splash-logo {
    width: 96px;
    height: 96px;
    object-fit: contain;
    filter: drop-shadow(0 0 32px rgba(212, 162, 76, 0.5));
}

.splash-text {
    font-size: 0.95rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.7);
    letter-spacing: 0.04em;
}

.splash-dots {
    display: flex;
    gap: 6px;
}

.splash-dots span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--gold);
    animation: dot-bounce 1.2s ease-in-out infinite;
}

.splash-dots span:nth-child(2) { animation-delay: 0.2s; }
.splash-dots span:nth-child(3) { animation-delay: 0.4s; }

@keyframes dot-bounce {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
    40%            { transform: scale(1);   opacity: 1; }
}

@keyframes splash-pop {
    0%   { opacity: 0; transform: scale(0.8); }
    100% { opacity: 1; transform: scale(1); }
}

.splash-enter-active { transition: opacity 0.25s ease; }
.splash-leave-active { transition: opacity 0.4s ease; }
.splash-enter-from,
.splash-leave-to     { opacity: 0; }

/* ── Full‑page wallpaper ── */
.bg-wallpaper {
    position: fixed;
    inset: 0;
    z-index: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0.12;
    filter: blur(6px) saturate(0.3);
    pointer-events: none;
}

.auth-bg {
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
}

.blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(90px);
    opacity: 0.25;
    will-change: transform;
}

.blob--gold {
    width: 500px;
    height: 500px;
    background: var(--gold);
    top: -200px;
    left: -150px;
    animation: drift-a 22s ease-in-out infinite alternate;
}

.blob--teal {
    width: 550px;
    height: 550px;
    background: var(--teal);
    bottom: -200px;
    right: -150px;
    animation: drift-b 26s ease-in-out infinite alternate;
}

@keyframes drift-a {
    0% { transform: translate(0, 0); }
    100% { transform: translate(80px, 60px); }
}
@keyframes drift-b {
    0% { transform: translate(0, 0); }
    100% { transform: translate(-70px, -80px); }
}

.frost-field {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.frost-fleck {
    position: absolute;
    top: -10px;
    border-radius: 50%;
    background: radial-gradient(circle, var(--fleck-color), transparent 70%);
    opacity: 0;
    will-change: transform, opacity;
    animation: fall-drift 20s ease-in-out infinite;
}

@keyframes fall-drift {
    0%   { transform: translate3d(0, 0, 0); opacity: 0; }
    12%  { opacity: 0.8; }
    50%  { transform: translate3d(var(--drift-x), 55vh, 0); opacity: 0.5; }
    88%  { opacity: 0.2; }
    100% { transform: translate3d(var(--drift-x), 110vh, 0); opacity: 0; }
}

.login-enter-active {
    animation: login-in 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}
.login-leave-active {
    animation: login-out 0.4s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

@keyframes login-in {
    0% { opacity: 0; transform: scale(0.96) translateY(30px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}

@keyframes login-out {
    0% { opacity: 1; transform: scale(1) translateY(0); }
    100% { opacity: 0; transform: scale(0.96) translateY(-30px); }
}

.field--stagger {
    opacity: 0;
    animation: fade-up 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    animation-delay: 0.3s;
}

@keyframes fade-up {
    0% { opacity: 0; transform: translateY(16px); }
    100% { opacity: 1; transform: translateY(0); }
}

.auth-grid {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 1fr 1fr;
    max-width: 1100px;
    width: 100%;
    min-height: 600px;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(28px) saturate(180%);
    -webkit-backdrop-filter: blur(28px) saturate(180%);
    border-radius: 32px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow:
        0 30px 80px rgba(0, 0, 0, 0.6),
        inset 0 1px 0 rgba(255, 255, 255, 0.18),
        inset 0 0 60px rgba(255, 255, 255, 0.03);
    overflow: hidden;
    isolation: isolate;
}

.grain {
    position: absolute;
    inset: 0;
    z-index: 5;
    pointer-events: none;
    opacity: 0.05;
    mix-blend-mode: overlay;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
}

.auth-grid::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -60%;
    width: 40%;
    height: 200%;
    background: linear-gradient(
        115deg,
        transparent 0%,
        rgba(255, 255, 255, 0.09) 45%,
        rgba(255, 255, 255, 0.14) 50%,
        rgba(255, 255, 255, 0.09) 55%,
        transparent 100%
    );
    transform: rotate(8deg);
    animation: shimmer-sweep 9s ease-in-out infinite;
    pointer-events: none;
    z-index: 4;
}

@keyframes shimmer-sweep {
    0%, 35% { left: -60%; }
    65%, 100% { left: 140%; }
}

.brand-panel {
    position: relative;
    overflow: hidden;
    background: rgba(0, 0, 0, 0.22);
    padding: 3rem 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-right: 1px solid rgba(255, 255, 255, 0.08);
}

.brand-watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 70%;
    max-width: 350px;
    height: auto;
    opacity: 0.16;
    pointer-events: none;
    z-index: 0;
    filter: drop-shadow(0 0 40px rgba(212, 162, 76, 0.2));
}

.brand-content {
    position: relative;
    z-index: 1;
    max-width: 360px;
    text-align: center;
}

.brand-title {
    font-size: 3.2rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    background: linear-gradient(135deg, #f0ede8, #d4a24c);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 0.25rem;
}

.brand-subtitle {
    font-size: 0.9rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

.brand-divider {
    width: 60px;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--gold), transparent);
    margin: 0 auto 1.25rem;
}

.brand-tagline {
    font-size: 1rem;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.6);
    font-weight: 400;
}

.form-panel {
    position: relative;
    z-index: 1;
    padding: 3rem 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-card {
    width: 100%;
    max-width: 380px;
}

.form-header {
    margin-bottom: 2rem;
}

.form-header h2 {
    font-size: 1.8rem;
    font-weight: 600;
    letter-spacing: -0.02em;
    color: #fff;
    margin-bottom: 0.3rem;
}

.form-header p {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.field {
    display: flex;
    flex-direction: column;
}

.field label {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-muted);
    margin-bottom: 0.4rem;
}

.field__control {
    position: relative;
    display: flex;
    align-items: center;
}

.field__control input {
    width: 100%;
    background: rgba(255, 255, 255, 0.06);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    border: 1.5px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 0.7rem 1rem;
    padding-right: 2.8rem;
    font-size: 0.95rem;
    color: #fff;
    outline: none;
    transition: border-color 0.25s, box-shadow 0.25s, background 0.25s;
    font-family: 'Inter', sans-serif;
}

.field__control input::placeholder {
    color: rgba(255, 255, 255, 0.28);
}

.field__control input:focus {
    background: rgba(255, 255, 255, 0.09);
    border-color: var(--gold);
    box-shadow:
        0 0 0 3px rgba(212, 162, 76, 0.16),
        0 0 24px rgba(212, 162, 76, 0.12);
}

.field__control input.has-error {
    border-color: var(--error);
}

.toggle-visibility {
    position: absolute;
    right: 0.8rem;
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s;
}

.toggle-visibility:hover { color: #fff; }

.toggle-visibility svg {
    width: 18px;
    height: 18px;
}

.error {
    color: var(--error);
    font-size: 0.75rem;
    margin-top: 0.3rem;
}

.field--inline {
    flex-direction: row;
    align-items: center;
}

.checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.85rem;
    color: var(--text-muted);
}

.checkbox input {
    accent-color: var(--gold);
    width: 16px;
    height: 16px;
}

.submit-btn {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, var(--gold), #b8893a);
    color: #0e1a18;
    border: none;
    padding: 0.85rem;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.3s;
    margin-top: 0.5rem;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.3);
}

.submit-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow:
        0 8px 25px rgba(212, 162, 76, 0.35),
        inset 0 1px 0 rgba(255, 255, 255, 0.35);
}

.submit-btn:active:not(:disabled) { transform: scale(0.98); }

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
}

.ripple-circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: scale(0);
    pointer-events: none;
    animation: ripple-expand 600ms ease-out;
}

@keyframes ripple-expand {
    to { transform: scale(2.8); opacity: 0; }
}

.spinner {
    width: 18px;
    height: 18px;
    animation: rotate-spin 0.8s linear infinite;
}

@keyframes rotate-spin {
    to { transform: rotate(360deg); }
}

.form-footer {
    margin-top: 2rem;
    text-align: center;
    font-size: 0.7rem;
    color: var(--text-muted);
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

.footer-separator { opacity: 0.4; }

@media (max-width: 820px) {
    .auth-grid {
        grid-template-columns: 1fr;
        min-height: auto;
        border-radius: 24px;
    }
    .brand-panel {
        border-right: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding: 2.5rem 1.5rem;
    }
    .brand-content { max-width: 100%; }
    .brand-title { font-size: 2.8rem; }
    .brand-watermark { width: 50%; max-width: 200px; }
    .form-panel { padding: 2.5rem 1.5rem; }
    .form-card { max-width: 100%; }
}

@media (max-width: 480px) {
    .auth-container { padding: 1rem; }
    .auth-grid { border-radius: 20px; }
    .brand-panel { padding: 2rem 1.25rem; }
    .form-panel { padding: 2rem 1.25rem; }
    .form-header h2 { font-size: 1.5rem; }
    .brand-title { font-size: 2.4rem; }
    .brand-watermark { width: 60%; max-width: 150px; }
}

@media (prefers-reduced-motion: reduce) {
    .blob, .frost-fleck, .auth-grid::after, .spinner,
    .login-enter-active, .login-leave-active, .field--stagger,
    .splash-card, .splash-dots span {
        animation: none;
    }
    .field--stagger { opacity: 1; }
    .frost-fleck { display: none; }
    .auth-grid::after { display: none; }
}
</style>