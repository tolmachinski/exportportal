const fixGetNavigableIndexesSlick = slick => {
    // eslint-disable-next-line no-param-reassign
    slick.getNavigableIndexes = function getNavigableIndexes() {
        const that = this;
        let breakPoint = 0;
        let counter = 0;
        const indexes = [];
        let max;

        if (that.options.infinite === false) {
            max = that.slideCount;
        } else {
            breakPoint = that.slideCount * -1;
            counter = that.slideCount * -1;
            max = that.slideCount * 2;
        }

        while (breakPoint < max) {
            indexes.push(breakPoint);
            breakPoint = counter + that.options.slidesToScroll;
            counter += that.options.slidesToScroll <= that.options.slidesToShow ? that.options.slidesToScroll : that.options.slidesToShow;
        }

        return indexes;
    };
};

export default fixGetNavigableIndexesSlick;
