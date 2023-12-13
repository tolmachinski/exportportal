
module.exports = {
    userGroups: {
        verified:{
            class: "txt-green",
            text: {
                seller: "Verified Seller",
                buyer: "Buyer",
                manufacturer: "Verified Manufacturer",
                distributor: "Verified Seller - Distributor",
            }
        },
        certified:{
            class: "txt-orange",
            text: {
                seller: "Certified Seller",
                manufacturer: "Certified Manufacturer",
                distributor: "Certified Seller - Distributor",
            }
        }
    },
    dateFormat:{
        withTime:"31 Dec, 2020 23:59",
        withoutTime:"31 Dec, 2020",
        withInterval: "18 Jun-30 Sep, 2022",
        month: "December",
    },
    link: "http://exportportal.loc/backstop_test/pictures",
    name: {
        xLong: "Backstop extra very long name for tests",
        long: "Backstop very long name for tests",
        medium: "Backstop medium test name",
        short: "Backstop name"
    },
    price: {
        max: "€9,999,999 - €9,999,999",
        high: "€ 9,999,999",
        medium: "€ 9,999.99",
        low: "€ 99.99",
        min: "€ 9.99"
    },
    phone: "+7 (495) 937-99-92",
    mail: "backstoptest@test.com",
    discount: {
        max: "- 99%",
        high: "- 75%",
        medium: "- 50%",
        low: "- 25%",
        min: "- 1%",
        outOfStock: "OUT OF STOCK",
        samples: "SAMPLES ONLY",
    },
    country: {
        name: "Democratic republic of congo",
        location: "Kinshasa, Kinshasa",
        flag: "/public/img/flags/Democratic-Republic-of-the-Congo.svg",
    },
    tag: {
        10: "# Backstop",
        20: "# BackstopTestTagNam",
        30: "# BackstopTestTagNameWith30sym",
    },
    companyName: "Backstop Company Name",
    userOnlineStatus: "Online",
    textClassList: {
        textRed: "txt-red",
        textGreen: "txt-green",
    },
    systMessCardClass: ".system-messages__card",
    /**
    *set messages file.
    * @function Генератор lorem
    * @param n Number:Длина нужного вам текста
    */
    lorem: n => {
        let str = "Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue.";
        if(n > str.length){
            let nStr = str;
            while(nStr.length < n){
                nStr = nStr+str;
            }
            str = nStr;
        }
        return str.slice(0,n)
    },
    imgLocal: {
        '800x780': "/public/img/no_image/800x780.png"
    },
    /**
    *set messages file.
    * @function Генератор ссылок для картинок
    * @param w Number: Ширина
    * @param h Number: Высота
    */
    img: (w, h) => {
        return "https://dummyimage.com/"+w+"x"+h+"/000/fff"
    },
    getAuthData: type => {
        const data = {
            "buyer": {
                login: "backstop-buyer@automation.test",
                password: "QjjtiwZdNHVtFX9",
                cookie: "c8700de5ceda931f54fd3746aab808164cdab8f03cfdac4d92044375020ecd9835ae12100562ee618a32357468b6538df0d4de73fa2444ea11088cee491a7df415825aee15eb335cc13f9b559f166ee87xmeaoz4%24argon2id%24v%3D19%24m%3D65536%2Ct%3D4%2Cp%3D1%24dU1hNXhkVzV0LnE3aU1tUw%24Ud0CvIHhCDNCVmpqRf%2B2GQtSdE79AYjASY855KqHIpU",
            },
            "certified seller": {
                login: "backstop-1630574864619@backstop.test",
                password: "f3C7n4T2h9b0R3c1",
                cookie: "68dbe93fb8453872b90a8b5b91226a0d57144ef7553ce76beed3bda5b3bf485927aa5bcfc17e396a5dbdb49c1ab3002de21590cc2587af7aa2c744b3ee839a4b03227b950778ab86436ff79fe975b5966ydnrgf1%24argon2id%24v%3D19%24m%3D65536%2Ct%3D4%2Cp%3D1%24LzU0dC53UXpOME12dC9lcg%244%2BedTVX9KzgQ5hYIrexw%2BHYp60j3Fquv05RRIdBhczo",
            },
            "certified manufacturer": {
                login: "backstop-manufacturer@automation.test",
                password: "QjjtiwZdNHVtFX9",
                cookie: "2ee36967f5d43bd6778f3e66f668dd8747896e17f810ee56d71236e76607742a990f27042e1c668e9e8c52ef8920059eb549fca4dea2d7a3fa5551efe959781e5ef78f63ba22e7dfb2fa44613311b932cjh8kllf%24argon2id%24v%3D19%24m%3D65536%2Ct%3D4%2Cp%3D1%24SHlBN1VyTmp3QU1GRldFSw%247yQOoa5u%2FewaHgaPs8IDhVUBkNf863giEG10lJ3HNgg",
            },
            "freight forwarder": {
                login: "backstop-1603802699294@backstop.test",
                password: "f3C7n4T2h9b0R3c1",
                cookie: "3d81fb75124730746051127c47fdf12b39be9822cafebebedbb51d45fdaae09ab46559766ada1e486032f30efe6349ffc5db718c7471472f44bea60e24cf89c5fb2606a5068901da92473666256e6e5b8mrenr75%24argon2id%24v%3D19%24m%3D65536%2Ct%3D4%2Cp%3D1%24c2wuekxEWGc4Y1B2UGx0MQ%24z7fFsJGkYiEMZ9Az54ddusRUyP72TcbvCBE1CrPlRQM",
            },
        };

        return data[type];
    },
}
