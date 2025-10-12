/**
 * Keyboard Shortcuts Manager
 * Provides global keyboard shortcuts for power users
 */

class KeyboardShortcuts {
    constructor() {
        this.shortcuts = new Map();
        this.lastKey = null;
        this.lastKeyTime = 0;
        this.sequenceTimeout = 1000; // 1 second for key sequences
        this.init();
    }

    init() {
        document.addEventListener("keydown", (e) => this.handleKeyPress(e));
        this.registerGlobalShortcuts();
    }

    handleKeyPress(e) {
        // Don't trigger shortcuts when typing in inputs, textareas, or contenteditable
        const target = e.target;
        if (
            target.tagName === "INPUT" ||
            target.tagName === "TEXTAREA" ||
            target.isContentEditable
        ) {
            // Allow Escape key in inputs
            if (e.key !== "Escape") {
                return;
            }
        }

        const key = this.getKeyString(e);

        // Check for sequence shortcuts (like g+d)
        const now = Date.now();
        if (this.lastKey && now - this.lastKeyTime < this.sequenceTimeout) {
            const sequenceKey = `${this.lastKey}+${key}`;
            const sequenceHandler = this.shortcuts.get(sequenceKey);
            if (sequenceHandler) {
                e.preventDefault();
                sequenceHandler(e);
                this.lastKey = null;
                return;
            }
        }

        // Check for single key shortcuts
        const handler = this.shortcuts.get(key);
        if (handler) {
            e.preventDefault();
            handler(e);
            // Track this key for potential sequences
            this.lastKey = key;
            this.lastKeyTime = now;
        } else {
            // Track for sequence shortcuts
            this.lastKey = key;
            this.lastKeyTime = now;
        }
    }

    getKeyString(e) {
        const parts = [];
        if (e.ctrlKey || e.metaKey) parts.push("ctrl");
        if (e.altKey) parts.push("alt");
        if (e.shiftKey) parts.push("shift");
        parts.push(e.key.toLowerCase());
        return parts.join("+");
    }

    register(keys, handler, description = "") {
        if (Array.isArray(keys)) {
            keys.forEach((key) =>
                this.shortcuts.set(key.toLowerCase(), handler)
            );
        } else {
            this.shortcuts.set(keys.toLowerCase(), handler);
        }
    }

    registerGlobalShortcuts() {
        // Command Palette (Ctrl+K or Cmd+K)
        this.register(
            "ctrl+k",
            (e) => {
                this.toggleCommandPalette();
            },
            "Open command palette"
        );

        // Search (/)
        this.register(
            "/",
            (e) => {
                this.focusSearch();
            },
            "Focus search"
        );

        // Help (?)
        this.register(
            "shift+/",
            (e) => {
                this.toggleHelpModal();
            },
            "Show keyboard shortcuts"
        );

        // Escape key
        this.register(
            "escape",
            (e) => {
                this.handleEscape();
            },
            "Close modals/palettes"
        );

        // Navigation shortcuts
        this.register(
            "g+d",
            () => window.Livewire.navigate("/dashboard"),
            "Go to Dashboard"
        );
        this.register(
            "g+c",
            () => window.Livewire.navigate("/customers"),
            "Go to Customers"
        );
        this.register(
            "g+t",
            () => window.Livewire.navigate("/tickets"),
            "Go to Tickets"
        );
        this.register(
            "g+i",
            () => window.Livewire.navigate("/inventory"),
            "Go to Inventory"
        );
        this.register(
            "g+v",
            () => window.Livewire.navigate("/invoices"),
            "Go to Invoices"
        );
        this.register(
            "g+p",
            () => window.Livewire.navigate("/pos"),
            "Go to POS"
        );

        // Context shortcuts (these will be overridden by page-specific ones)
        this.register("n", () => this.createNew(), "Create new item");
        this.register("e", () => this.editCurrent(), "Edit current item");
    }

    toggleCommandPalette() {
        // Use Livewire's dispatch method directly
        if (window.Livewire) {
            window.Livewire.dispatch("toggle-command-palette");
        }
    }

    toggleHelpModal() {
        if (window.Livewire) {
            window.Livewire.dispatch("toggle-shortcuts-help");
        }
    }

    focusSearch() {
        // Find the first search input on the page
        const searchInput = document.querySelector(
            'input[type="search"], input[placeholder*="Search" i], input[placeholder*="search" i]'
        );
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }

    handleEscape() {
        // Dispatch event to close any open modals
        window.dispatchEvent(new CustomEvent("close-modals"));

        // Blur active element
        if (document.activeElement) {
            document.activeElement.blur();
        }
    }

    createNew() {
        // Try to find and click the "New" or "Create" button
        const createButton = document.querySelector('[href*="/create"]');
        if (createButton) {
            createButton.click();
        }
    }

    editCurrent() {
        // Try to find and click the first "Edit" button
        const editButton = document.querySelector('[href*="/edit"]');
        if (editButton) {
            editButton.click();
        }
    }

    getAllShortcuts() {
        return Array.from(this.shortcuts.entries()).map(([key, handler]) => ({
            key,
            description: handler.description || "",
        }));
    }
}

// Initialize keyboard shortcuts when DOM and Livewire are ready
document.addEventListener("DOMContentLoaded", function () {
    // Simple initialization - just create the instance
    window.keyboardShortcuts = new KeyboardShortcuts();
});

export default KeyboardShortcuts;
