<script setup lang="ts">
import FlashToast from '@/components/FlashToast.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const img = (n: string) => `/slice/assets/images/${n}`;

onMounted(() => {
    document.body.style.overflowY = 'auto';
});
onBeforeUnmount(() => {
    document.body.style.overflowY = '';
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', { onError: () => form.reset('password') });
};
</script>

<template>
    <Head title="تسجيل الدخول">
        <link rel="stylesheet" href="/slice/assets/vendor/bootstrap/bootstrap.min.css" />
        <link rel="stylesheet" href="/slice/assets/css/style.css" />
    </Head>

    <FlashToast />

    <main class="auth_split">
        <section class="auth_split__form">
            <div class="auth_split__form-inner">
                <Link href="/" class="auth_split__home d-inline-flex align-items-center gap-2 mb_24">
                    <span aria-hidden="true">→</span> العودة للصفحة الرئيسية
                </Link>

                <h1 class="fs-32 dark-color fw-bold mb_8">تسجيل الدخول</h1>
                <p class="dark-color mb_32" style="opacity: 0.65">مرحبًا بعودتك، سجّل دخولك للمتابعة إلى حسابك.</p>

                <div v-if="status" class="auth_split__status mb_24">{{ status }}</div>

                <form class="login" @submit.prevent="submit" novalidate>
                    <div class="form-group">
                        <label for="email">البريد الإلكتروني</label>
                        <img :src="img('mail.png')" alt="" />
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="form-control"
                            autocomplete="email"
                            autofocus
                            placeholder="البريد الإلكتروني"
                        />
                        <small v-if="form.errors.email" class="red-color d-block mt_8">{{ form.errors.email }}</small>
                    </div>

                    <div class="form-group">
                        <label for="password">كلمة المرور</label>
                        <img :src="img('login-lock.png')" alt="" />
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="form-control"
                            autocomplete="current-password"
                            placeholder="كلمة المرور"
                        />
                        <small v-if="form.errors.password" class="red-color d-block mt_8">{{ form.errors.password }}</small>
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb_24">
                        <label class="auth_split__remember d-inline-flex align-items-center gap-2 mb-0">
                            <input v-model="form.remember" type="checkbox" />
                            <span class="dark-color fs-14">تذكرني</span>
                        </label>
                        <Link v-if="canResetPassword" href="/forgot-password" class="main-color fs-14">نسيت كلمة المرور؟</Link>
                    </div>

                    <button type="submit" class="main_btn w-100 m-0 shadow d-flex align-items-center justify-content-center gap-2" :disabled="form.processing">
                        <span v-if="form.processing" class="auth_split__spinner" aria-hidden="true"></span>
                        {{ form.processing ? 'جاري تسجيل الدخول...' : 'دخول' }}
                    </button>
                </form>

                <div class="auth_split__divider mt_32 mb_24"><span>أو</span></div>

                <p class="dark-color fs-14 text-center mb_12">ليس لديك حساب؟ أنشئ حسابك الآن</p>
                <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                    <Link href="/register?role=provider" class="main_btn second_light d-flex align-items-center justify-content-center gap-2">
                        <img :src="img('user-blue.png')" alt="" class="m-0" /> مقدم خدمة - مورد
                    </Link>
                    <Link href="/register?role=client" class="main_btn light d-flex align-items-center justify-content-center gap-2">
                        <img :src="img('user-green.png')" alt="" class="m-0" /> طالب خدمة - مستفيد
                    </Link>
                </div>
            </div>
        </section>

        <aside class="auth_split__brand">
            <div class="auth_split__brand-inner">
                <div class="auth_split__logo">
                    <img :src="img('logo.png')" alt="منصة اتفاق" />
                </div>
                <h2 class="auth_split__title">منصة اتفاق</h2>
                <p class="auth_split__tagline">منصة المنافسات والمناقصات الإلكترونية التي تجمع طالبي الخدمة بمقدميها في مكان واحد.</p>

                <ul class="auth_split__points">
                    <li>
                        <span class="auth_split__check" aria-hidden="true">✓</span>
                        انشر منافساتك واستقبل عروض الموردين
                    </li>
                    <li>
                        <span class="auth_split__check" aria-hidden="true">✓</span>
                        قدّم عروضك على المنافسات بكل سهولة
                    </li>
                    <li>
                        <span class="auth_split__check" aria-hidden="true">✓</span>
                        عقود إلكترونية موثّقة بين الطرفين
                    </li>
                </ul>
            </div>
        </aside>
    </main>
</template>

<style scoped>
.auth_split {
    display: flex;
    min-height: 100vh;
    background: #fff;
    font-family: inherit;
}

.auth_split__form {
    flex: 1 1 54%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 24px;
}

.auth_split__form-inner {
    width: 100%;
    max-width: 430px;
}

.auth_split__home {
    color: #1c9c55;
    font-size: 14px;
    text-decoration: none;
    transition: opacity 0.2s ease;
}
.auth_split__home:hover {
    opacity: 0.7;
}
.auth_split__home span {
    font-size: 18px;
    line-height: 1;
}

.auth_split__status {
    background: #e9f8ef;
    color: #1c7a43;
    border: 1px solid #bfe9cf;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 600;
}

.auth_split__remember input[type='checkbox'] {
    width: 18px;
    height: 18px;
    accent-color: #1c9c55;
    cursor: pointer;
}

.auth_split__spinner {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-top-color: #fff;
    border-radius: 50%;
    display: inline-block;
    animation: auth_split_spin 0.6s linear infinite;
}
@keyframes auth_split_spin {
    to {
        transform: rotate(360deg);
    }
}

.auth_split__divider {
    display: flex;
    align-items: center;
    text-align: center;
    color: #a8aeb8;
    font-size: 13px;
}
.auth_split__divider::before,
.auth_split__divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e6e9ee;
}
.auth_split__divider span {
    padding: 0 16px;
}

.auth_split__brand {
    flex: 1 1 46%;
    position: relative;
    overflow: hidden;
    color: #fff;
    background: linear-gradient(150deg, #1fae5f 0%, #1c9c55 45%, #137a42 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 56px 48px;
}
.auth_split__brand::before,
.auth_split__brand::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    pointer-events: none;
}
.auth_split__brand::before {
    width: 340px;
    height: 340px;
    top: -130px;
    inset-inline-start: -110px;
}
.auth_split__brand::after {
    width: 240px;
    height: 240px;
    bottom: -90px;
    inset-inline-end: -70px;
}

.auth_split__brand-inner {
    position: relative;
    z-index: 1;
    max-width: 380px;
}

.auth_split__logo {
    display: inline-flex;
    background: #fff;
    border-radius: 16px;
    padding: 14px 20px;
    margin-bottom: 28px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
}
.auth_split__logo img {
    height: 46px;
    width: auto;
    display: block;
}

.auth_split__title {
    font-size: 30px;
    font-weight: 700;
    margin-bottom: 12px;
    color: #fff;
}
.auth_split__tagline {
    font-size: 16px;
    line-height: 1.9;
    margin-bottom: 32px;
    color: rgba(255, 255, 255, 0.9);
}

.auth_split__points {
    list-style: none;
    margin: 0;
    padding: 0;
}
.auth_split__points li {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 15px;
    line-height: 1.6;
    margin-bottom: 18px;
    color: rgba(255, 255, 255, 0.95);
}
.auth_split__check {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.18);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    flex-shrink: 0;
}

@media (max-width: 991px) {
    .auth_split {
        flex-direction: column-reverse;
    }
    .auth_split__brand {
        flex: none;
        padding: 40px 28px;
        text-align: center;
    }
    .auth_split__brand-inner {
        max-width: 520px;
        margin: 0 auto;
    }
    .auth_split__points {
        display: none;
    }
    .auth_split__tagline {
        margin-bottom: 0;
    }
    .auth_split__form {
        flex: none;
        padding: 40px 24px;
    }
}

@media (max-width: 575px) {
    .auth_split__title {
        font-size: 24px;
    }
    .auth_split__logo img {
        height: 38px;
    }
}
</style>
