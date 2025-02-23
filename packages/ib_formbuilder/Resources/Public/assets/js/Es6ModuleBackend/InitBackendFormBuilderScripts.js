// Import MyFormBuilderBackend plugin (no need to import jQuery twice)
import MyFormBuilderBackend from '@rms/mfbb';

class InitBackendFormBuilderScripts {
    constructor() {
        this.init(); // Initialize asynchronously
    }

    async init() {
        // Load jQuery and set it globally (i.e. for jquery-ui)
        const jQueryModule = await import("jquery");
        const $ = jQueryModule.default;
        window.jQuery = $;
        window.$ = $;

        // Load jQuery UI Sortable after jQuery is available globally
        // see cms-core/Resources/Public/JavaScript/Contrib/jquery-ui
        await import("jquery-ui/sortable.js");

        // Ensure that $.fn exists by explicitly setting it if needed
        if (typeof $.fn === 'undefined') {
            $.fn = jQuery.fn;
        }

        // Attach MyFormBuilderBackend to $.fn
        $.fn.MyFormBuilderBackend = MyFormBuilderBackend;

        if ($('form.tx_ibformbuilder-submit-external').length) {
            $('form.tx_ibformbuilder-submit-external').MyFormBuilderBackend({ testvar: 'formbuilder test' });
        }
    }
}

export default new InitBackendFormBuilderScripts();
