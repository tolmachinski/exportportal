import $ from "jquery";
import mix from "@src/util/common/mix";
import SampleOrdersDashboardModule from "@src/pages/sample_orders/my/fragments/classes/sample-orders-dashboard-module";

export default params => {
    // eslint-disable-next-line no-new
    new SampleOrdersDashboardModule(params);

    mix(globalThis, { noopFilterCallback: $.noop });
};
