// @ts-nocheck
import delay from "@src/util/async/delay";

const newYearSnowFlakes = async () => {
    await delay(5000);
    import("@scss/components/holidays/new-year-snow-flakes.scss");
    const { default: boot } = await import("@src/components/holidays/util/snow-flakes");
    boot();
};

export default newYearSnowFlakes;
