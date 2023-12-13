module.exports = async page => {
    // Stop all sliders
    await page.waitForFunction(() => {
        return (async () => {
            // Slick
            document.querySelectorAll(".slick-slider, .slick-initialized").forEach(slider => {
                slider.slick.defaults.autoplay = false;
                slider.slick.options.autoplay = false;
                // Stop auto play
                slider?.slick?.pause();
                // Go to first slide
                slider?.slick?.goTo(0);
            });

            // bxSlider
            document.querySelectorAll("[data-slider]").forEach(slider => {
                // Stop auto play
                slider?.bxSlider?.stopAuto();
                slider?.bxSlider?.goToSlide(0);
            });

            // Swiper
            await new Promise(resolve => {
                const sliderNodeList = document.querySelectorAll(".swiper-container, .swiper-container-initialized");
                if (!sliderNodeList.length) {
                    resolve();
                };

                sliderNodeList.forEach(slider => {
                    const stopSlider = () => {
                        slider.swiper.slideTo(2);
                        slider.swiper.autoplay.stop();
                        setTimeout(() => {
                            slider.swiper.slideTo(1);
                            slider.swiper.autoplay.stop();
                            resolve();
                        }, 500);
                    };

                    const currentImage = slider.querySelector(".swiper-slide-active img");
                    if (currentImage.complete) {
                        stopSlider();
                    } else {
                        currentImage.onload = () => {
                            stopSlider();
                        };
                    }
                });
            });

            return true;
        })();
    });
};
