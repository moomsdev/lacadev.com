/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    // Gutenberg blocks — PHP render templates + JS/SCSS
    './block-gutenberg/**/*.{js,jsx,php,scss}',
    // Theme templates (Twig / PHP)
    './app/**/*.php',
    './resources/**/*.{js,jsx,php,twig,html}',
  ],
  theme: {
    // Sync breakpoints với SCSS _variables.scss
    screens: {
      xs: '576px',
      sm: '768px',
      md: '992px',
      lg: '1200px',
      xl: '1440px',
    },
    extend: {
      // Màu sắc reference CSS custom properties từ Carbon Fields
      // Khi admin thay màu trong backend → Tailwind classes tự cập nhật
      colors: {
        primary:   'var(--primary-color)',
        secondary: 'var(--secondary-color)',
        bg:        'var(--bg-color)',
        'primary-dark':   'var(--primary-color-dark)',
        'secondary-dark': 'var(--secondary-color-dark)',
        'bg-dark':        'var(--bg-color-dark)',
        // Admin colors
        'primary-ad':   'var(--primary-color-ad)',
        'secondary-ad': 'var(--secondary-color-ad)',
        'bg-ad':        'var(--bg-color-ad)',
        'text-ad':      'var(--text-color-ad)',
      },
      // Sync fonts với SCSS $primaryFont, $secondaryFont
      fontFamily: {
        primary:   ['"Be Vietnam Pro"', 'sans-serif'],
        secondary: ['Quicksand', 'sans-serif'],
        sans:      ['Quicksand', '"Be Vietnam Pro"', 'sans-serif'],
      },
      // Container max-width sync với $container-mw: 90rem
      maxWidth: {
        container: '90rem',
      },
      // Font weights sync với SCSS variables
      fontWeight: {
        thin:        '100',
        extralight:  '200',
        light:       '300',
        normal:      '400',
        medium:      '500',
        semibold:    '600',
        bold:        '700',
        extrabold:   '800',
        black:       '900',
      },
    },
  },
  plugins: [],
};
