import { SUBDOMAIN_URL } from "@src/common/constants";

const ajaxCheckSession = async () => {
    const { default: postRequest } = await import("@src/util/http/post-request");
    postRequest(`${SUBDOMAIN_URL}authenticate/checkSession`);
};

const checkSession = () => setInterval(() => ajaxCheckSession(), 600000);

export default checkSession;
