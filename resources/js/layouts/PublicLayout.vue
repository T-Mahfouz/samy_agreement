<script setup lang="ts">
import FlashToast from '@/components/FlashToast.vue';
import NotificationBell from '@/components/NotificationBell.vue';
import { type SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// style.css يقفل التمرير على body؛ في الأصل main.js يفتحه: $("body").css({ "overflow-y": "visible" })
onMounted(() => {
    document.body.style.overflowY = 'visible';
});
onBeforeUnmount(() => {
    document.body.style.overflowY = '';
});

const page = usePage<SharedData>();
const user = computed(() => page.props.auth?.user);

const img = (name: string) => `/slice/assets/images/${name}`;

const loginRef = ref<HTMLElement | null>(null);
const regRef = ref<HTMLElement | null>(null);
const loginOpen = ref(false);
const regOpen = ref(false);
const menuOpen = ref(false);

onClickOutside(loginRef, () => (loginOpen.value = false));
onClickOutside(regRef, () => (regOpen.value = false));

const isNavActive = (key: string) => {
    const url = page.url;
    if (key === 'home') return url === '/' || url.startsWith('/tenders');
    if (key === 'about') return url.startsWith('/about');
    if (key === 'faqs') return url.startsWith('/faqs') || url.startsWith('/contact');
    return false;
};

const dashboardHref = computed(() => (user.value?.role === 'admin' ? '/admin/dashboard' : '/dashboard'));
const roleLabel = computed(() => (user.value?.role === 'provider' ? 'إسم المورد' : user.value?.role === 'client' ? 'إسم المستفيد' : 'مرحبًا'));

const loginForm = useForm({ email: '', password: '', remember: false });
const submitLogin = () => loginForm.post('/login', { onError: () => (loginForm.password = '') });
</script>

<template>
    <Head>
        <link rel="stylesheet" href="/slice/assets/vendor/bootstrap/bootstrap.min.css" />
        <link rel="stylesheet" href="/slice/assets/css/style.css" />
    </Head>

    <FlashToast />

    <!-- Header -->
    <header>
        <div class="container">
            <div class="row">
                <div class="col-12 d-flex align-items-end justify-content-between">
                    <Link href="/" class="logo">
                        <img :src="img('logo.png')" class="logo" alt="logo" />
                    </Link>
                    <div class="btns d-flex align-items-center gap-4">
                        <template v-if="user">
                            <NotificationBell />
                            <p class="d-inline-flex flex-column mb-0 me_12">
                                <span class="fs-14 dark-color">{{ roleLabel }}</span>
                                <span class="fs-14 main-color fw-bold">{{ user.name }}</span>
                            </p>
                            <Link :href="dashboardHref"
                                class="main_btn second d-inline-flex align-items-center justify-content-center shadow">
                                <img :src="img('user.png')" alt="">
                                <span>لوحة التحكم</span>
                            </Link>
                        </template>
                        <template v-else>
                            <div class="dropdown" :class="{ show: loginOpen }" ref="loginRef">
                                <a href="#" role="button"
                                    class="main_btn login_dropdown_toggle dropdown-toggle d-inline-flex align-items-center justify-content-center shadow"
                                    @click.prevent="loginOpen = !loginOpen; regOpen = false">
                                    <img :src="img('login.png')" alt="">
                                    <span>دخول</span>
                                </a>
                                <form class="dropdown-menu dropdown-menu-right login shadow" :class="{ show: loginOpen }"
                                    @submit.prevent="submitLogin">
                                    <div class="form-group">
                                        <img :src="img('login-user.png')" alt="">
                                        <input v-model="loginForm.email" type="email" class="form-control" placeholder="البريد الإلكتروني">
                                    </div>
                                    <div class="form-group">
                                        <img :src="img('login-lock.png')" alt="">
                                        <input v-model="loginForm.password" type="password" class="form-control" placeholder="كلمة المرور">
                                    </div>
                                    <div class="form-group d-flex align-items-center justify-content-end m-0">
                                        <button type="submit" class="main_btn m-0" :disabled="loginForm.processing">دخول</button>
                                    </div>
                                </form>
                            </div>
                            <div class="dropdown" :class="{ show: regOpen }" ref="regRef">
                                <a href="#" role="button"
                                    class="main_btn second login_dropdown_toggle dropdown-toggle d-inline-flex align-items-center justify-content-center shadow"
                                    @click.prevent="regOpen = !regOpen; loginOpen = false">
                                    <img :src="img('user.png')" alt="">
                                    <span> إنشاء حساب</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right reg shadow" :class="{ show: regOpen }">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <Link href="/register?role=provider"
                                            class="main_btn second_light d-flex align-items-center justify-content-center gap-2">
                                            <img :src="img('user-blue.png')" alt="">
                                            مقدم خدمة - مورد </Link>
                                        <Link href="/register?role=client"
                                            class="main_btn light d-flex align-items-center justify-content-center gap-2">
                                            <img :src="img('user-green.png')" alt="">
                                            طالب خدمة - مستفيد </Link>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <button class="menu_btn" type="button" @click="menuOpen = !menuOpen">
                            <svg width="18" height="12" viewBox="0 0 18 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0H18V2H0V0ZM0 5H18V7H0V5ZM0 10H18V12H0V10Z" fill="#ffffff" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <div class="collapse navbar-collapse" :class="{ show: menuOpen }" id="main_nav">
                    <ul class="nav">
                        <li><Link href="/" class="d-flex align-items-center gap-4" :class="{ active: isNavActive('home') }">
                                <div class="img_box d-flex align-items-center justify-content-center">
                                    <img :src="img('menu-icon-1.png')" alt="">
                                </div> المنافسات
                            </Link>
                        </li>
                        <li><Link href="/about" class="d-flex align-items-center gap-4" :class="{ active: isNavActive('about') }">
                                <div class="img_box d-flex align-items-center justify-content-center">
                                    <img :src="img('menu-icon-2.png')" alt="">
                                </div> عن المنصة
                            </Link>
                        </li>
                        <li><Link href="/faqs" class="d-flex align-items-center gap-4" :class="{ active: isNavActive('faqs') }">
                                <div class="img_box d-flex align-items-center justify-content-center">
                                    <img :src="img('menu-icon-3.png')" alt="">
                                </div> المساعدة
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Page Content -->
    <div class="page_content">
        <slot />
    </div>

    <!-- Footer -->
    <footer>
        <div class="topbar main_bc p_16">
            <ul class="d-flex align-items-center flex-wrap justify-content-center">
                <li class="me_16"><Link class="mt_4 mb_4 d-block white-color fs-16 pe_16" href="/faqs">الأسئلة الشائعة</Link></li>
                <li class="me_16"><Link class="mt_4 mb_4 d-block white-color fs-16 pe_16" href="/terms">شروط الاستخدام وأداء المسؤولية</Link></li>
                <li class="me_16"><Link class="mt_4 mb_4 d-block white-color fs-16 pe_16" href="/privacy">سياسة الخصوصية</Link></li>
                <li class="me_16"><Link class="mt_4 mb_4 d-block white-color fs-16 pe_16" href="/contact">تواصل معنا</Link></li>
            </ul>
        </div>
        <div class="container pt_48 pb_48">
            <div class="row align-items-center">
                <div class="col-12 col-md-7 col-sm-6 col-lg-4">
                    <div class="brand d-flex align-items-center justify-content-center gap-2">
                        <Link class="logo" href="/" aria-label="AGREEMENT">
                            <img :src="img('logo.png')" alt="AGREEMENT" class="m-0">
                        </Link>
                        <div class="copy text-center w-100">
                            <p class="mb_0 fs-14">جميع الحقوق محفوظة © منصة اتفاق AGREEMENT</p>
                            <p class="mb_0 fs-14 ltr">www.domainname.com</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-5 col-sm-6 col-lg-4">
                    <ul class="social d-flex align-items-center justify-content-center gap-2">
                        <li><a href="#" aria-label="YouTube"><img :src="img('youtube.png')" alt=""></a></li>
                        <li><a href="#" aria-label="LinkedIn"><img :src="img('linkedin.png')" alt=""></a></li>
                        <li><a href="#" aria-label="Instagram"><img :src="img('instagram.png')" alt=""></a></li>
                        <li><a href="#" aria-label="X"><img :src="img('x.png')" alt=""></a></li>
                    </ul>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="stores d-flex align-items-center justify-content-center gap-2">
                        <a class="store" href="#" aria-label="App Store"><img :src="img('apple.png')" alt="App Store"></a>
                        <a class="store" href="#" aria-label="Google Play"><img :src="img('google.png')" alt="Google Play"></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="contact_support pt_24 pb_24 text-center">
            <p class="text-center fs-16 mb_24">هل تحتاج الي مساعدة؟ <span class="main-color">نسعد بتواصلكم معنا</span></p>
            <Link href="/contact" class="main_btn light">إتصل بنا الان</Link>
        </div>
    </footer>
</template>
