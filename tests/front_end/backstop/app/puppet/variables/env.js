const ENV = {
    errorPercent: "3%", // Процент погрешности / 100
    subdomain: "$sbdmkey$",
    debugMode: false,
}

ENV.url = `https://${ENV.subdomain}ep-zone.net`;
ENV.referenceURL = `http://${ENV.subdomain}exportportal.loc`;

if (process.env["npm_lifecycle_script"]?.split(" ").find(e => e.match("local"))) {
    ENV.url = ENV.referenceURL;
}

module.exports = ENV;
