class WidthMonitor {
    constructor(statuses, initialWidth) {
        this.statuses = statuses || null;
        this.currentWidth = initialWidth || 0;
        this.statusVisibilityBreakpoint = undefined;
    }

    adjustTo(width) {
        const widthThis = width || 0;
        if (typeof widthThis !== "number") {
            throw new TypeError("The width must be a number");
        }
        if (widthThis === this.currentWidth) {
            return;
        }

        // Resize
        this.currentWidth = widthThis;
        // Show statuses if need
        if (this.statuses !== null && widthThis > this.statusVisibilityBreakpoint) {
            this.statuses.show();
        }
    }
}

export default WidthMonitor;
