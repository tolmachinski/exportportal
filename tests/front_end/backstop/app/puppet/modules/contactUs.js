module.exports = async (page, isContactPage = false) => {
    const variables = require("../variables/variables");
    const data = {
        fname: variables.name.medium,
        lname: variables.name.medium,
        email: `backstop-${new Date().getTime()}@backstop.test`,
        phone: 777777777,
        subject: variables.lorem(30),
        message: variables.lorem(200),
        isContactPage,
    };

    await page.waitForFunction(fillForm, {}, data);
};

function fillForm(data) {
    return (async function () {
        const prefix = data.isContactPage ? "" : "_popup";
        // Change user fname
        document.querySelector(`[atas="global__contact-us_fname-input${prefix}"]`).value = data.fname;
        // Change user lname
        document.querySelector(`[atas="global__contact-us_lname-input${prefix}"]`).value = data.lname;
        // Change phone number
        document.querySelector(`[atas="global__contact-us_phone-input${prefix}"]`).value = data.phone;
        // Change email
        document.querySelector(`[atas="global__contact-us_email-input${prefix}"]`).value = data.email;
        // Change subject
        document.querySelector(`[atas="global__contact-us_subject-input${prefix}"]`).value = data.subject;
        // Change message
        document.querySelector(`[atas="global__contact-us_message-textarea${prefix}"]`).value = data.message;
        // Ignore validation on mask
        document.querySelector(`[atas="global__contact-us_phone-input${prefix}"]`).removeAttribute("class");

        return true;
    })();
}
