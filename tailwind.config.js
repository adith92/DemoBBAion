/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
            colors: {
                // Dark Command Center palette
                'cc-bg':       '#09090f',
                'cc-surface':  '#0f0f1a',
                'cc-card':     '#131324',
                'cc-border':   'rgba(255,255,255,0.07)',
                'cc-cyan':     '#00e5ff',
                'cc-blue':     '#3b82f6',
                'cc-gold':     '#f59e0b',
                'cc-emerald':  '#10b981',
                'cc-red':      '#ef4444',
                'cc-purple':   '#8b5cf6',
                'cc-text':     '#e2e8f0',
                'cc-muted':    '#64748b',
                // Material Design 3 (preserved compatibility)
                'primary':     'rgb(var(--color-primary) / <alpha-value>)',
                'secondary':   'rgb(var(--color-secondary) / <alpha-value>)',
                'tertiary':    '#003c73',
                'surface':     '#f8f9ff',
                'on-surface':  '#0b1c30',
                'outline':     '#737783',
                'surface-container-low': '#eff4ff',
                'on-surface-variant':    '#434652',
                'surface-bright':        '#f8f9ff',
                // Stage colors for Kanban
                'stage-prospecting': '#3b82f6',
                'stage-proposal':    '#f59e0b',
                'stage-negotiation': '#f97316',
                'stage-won':         '#10b981',
                'stage-lost':        '#ef4444',
            },
            boxShadow: {
                'glow-cyan':  '0 0 20px rgba(0,229,255,0.15)',
                'glow-blue':  '0 0 20px rgba(59,130,246,0.15)',
                'card':       '0 1px 3px rgba(0,0,0,0.4), 0 1px 2px rgba(0,0,0,0.3)',
                'card-hover': '0 4px 12px rgba(0,0,0,0.5)',
            },
        },
    },
    plugins: [],
}
