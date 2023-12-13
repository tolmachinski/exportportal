<?php if (DEBUG_MODE) { ?>
    <script src="<?php echo fileModificationTime('public/plug/file-saver-2-0-2/CustomFileSaver.js'); ?>" async></script>
<?php } else { ?>
    <script src="<?php echo fileModificationTime('public/plug/file-saver-2-0-2/CustomFileSaver.min.js'); ?>" async></script>
<?php } ?>
<script>
    $(function() {
        var _global = typeof window === 'object' && window.window === window
            ? window
            : typeof self === 'object' && self.self === self ? self : typeof global === 'object' && global.global === global ? global : void 0;

        /** @deprecated */
        var download = function(url, name, opts, popup) {
            var xhr = new XMLHttpRequest(); // use sync to avoid popup blocker
            xhr.open('GET', url);
            xhr.responseType = 'blob';
            xhr.onload = function() { saveAsFallback(xhr.response, name, opts, popup); };
            xhr.onerror = function() { console.error('Failed to download file'); };
            xhr.send();
        };
        /** @deprecated */
        var saveAsFallback = function(blob, name, opts, popup) {
            // Open a popup immediately do go around popup blocker
            // Mostly only available on user interaction and the fileReader is async so...
            popup = popup || open('', '_blank');
            if (popup) {
                popup.document.title = popup.document.body.innerText = 'downloading...';
            }

            if (typeof blob === 'string') {
                return download(blob, name, opts, popup);
            }

            var force = blob.type === 'application/octet-stream';
            var isSafari = /constructor/i.test(_global.HTMLElement) || _global.safari;
            var isChromeIOS = /CriOS\/[\d]+/.test(navigator.userAgent);
            if ((isChromeIOS || force && isSafari) && typeof FileReader !== 'undefined') {
                // Safari doesn't allow downloading of blob URLs
                var reader = new FileReader();
                var onLoad = function() {
                    var url = reader.result;
                    url = isChromeIOS ? url : url.replace(/^data:[^;]*;/, 'data:attachment/file;');
                    if (popup) {
                        popup.location.href = url;
                    } else {
                        location = url;
                    }

                    popup = null; // reverse-tabnabbing #460
                };

                reader.onloadend = onLoad;
                reader.readAsDataURL(blob);
            } else {
                var URL = _global.URL || _global.webkitURL;
                var url = URL.createObjectURL(blob);
                if (popup) {
                    popup.location = url;
                } else {
                    location.href = url;
                }

                popup = null; // reverse-tabnabbing #460

                setTimeout(function() { URL.revokeObjectURL(url); }, 4E4); // 40s
            }
        };
        /** @deprecated */
        var downloadFallback = function(url, name) {
            if (typeof window !== 'object' || window !== _global) {
                return; // Gracefully fall, we are in web worker
            }

            saveAsFallback(url, name);
            // saveAs(url, name);
        };
        var downloadFile = function(url, name) {
            url = url || null;
            name = name || null;
            if (null === url) {
                return;
            }

            saveAs(url, name);

            // No longer need with custom file saver
            // if (isIoS() || isChromeIoS()) {
            //     downloadFallback(url, name);
            // } else {
            //     saveAs(url, name);
            // }
        };

        mix(_global, {
            downloadFile: downloadFile
        }, false);
    });
</script>