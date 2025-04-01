"use strict";

document.addEventListener("DOMContentLoaded", () => {
    // SWIPER TESTIMONIALS
    const swiper = new Swiper(".swiper-testimonials", {
        slidesPerView: 3,
        spaceBetween: 15,
        centeredSlides: true,
        loop: true,
        grabCursor: true,
        // breakpoints: {
        //     640: {
        //         slidesPerView: 2,
        //         spaceBetween: 20,
        //     },
        //     768: {
        //         slidesPerView: 4,
        //         spaceBetween: 40,
        //     },
        //     1024: {
        //         slidesPerView: 5,
        //         spaceBetween: 50,
        //     },
        // },
    });
    // SWIPER TESTIMONIALS

    // INITIALIZE AOS
    AOS.init({
        once: false,
        duration: 600,
    });
    // INITIALIZE AOS
});
