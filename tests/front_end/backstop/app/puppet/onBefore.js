module.exports = async (page, scenario, vp) => {
    await require('./loadCookies')(page, scenario);

    let ENV = require('./variables/env');
    if (ENV.debugMode) {
        console.log("onBefore, start...");
    }

    if(!scenario._url){
        const subdomain = (scenario.subdomain) ? scenario.subdomain + "." : "";
        const url = ENV.url.replace("$sbdmkey$", subdomain) + scenario.url;
        const referenceURL = ENV.referenceURL.replace("$sbdmkey$", subdomain) + scenario.url;
        scenario.url = url;

        if (process.argv.includes("reference")) {
            scenario.referenceUrl = referenceURL;
            scenario.url = referenceURL;
        }
    }

    scenario.misMatchThreshold = scenario.misMatchThreshold || parseInt(ENV.errorPercent) / 100;
    scenario.requireSameDimensions = scenario.requireSameDimensions || true;
    const url = new URL(scenario.url);
    url.searchParams.set("backstop", scenario.backstopVariant ?? 1);
    scenario.url = url.href;

    if(!scenario._url) {
        if (process.argv.includes("reference")) {
            const refUrl = new URL(scenario.referenceUrl)
            refUrl.searchParams.set("backstop", scenario.backstopVariant ?? 1);
            scenario.referenceUrl = refUrl.href;
        }

        scenario._url = true; // its meen what url was changes, because test multi resolution use this scenario to another resolution test
    }

    if (ENV.debugMode) {
        console.log("onBefore, success");
    }
};
