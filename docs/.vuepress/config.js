module.exports = {
    markdown: {
        anchor: { level: [2, 3] },
        extendMarkdown(md) {
            let markup = require('vuepress-theme-craftdocs/markup');
            md.use(markup);
        },
    },
    base: '/star-ratings/',
    title: 'Star Ratings plugin for Craft CMS',
    plugins: [
        [
            'vuepress-plugin-clean-urls',
            {
                normalSuffix: '/',
                indexSuffix: '/',
                notFoundPath: '/404.html',
            },
        ],
    ],
    theme: 'craftdocs',
    themeConfig: {
        codeLanguages: {
            php: 'PHP',
            twig: 'Twig',
            js: 'JavaScript',
        },
        logo: '/images/icon.svg',
        searchMaxSuggestions: 10,
        nav: [
            {text: 'Getting Started️', link: '/getting-started/'},
            {
                text: 'How It Works',
                items: [
                    {text: 'Display star ratings', link: '/display-star-ratings/'},
                    {text: 'Customize your star icons', link: '/customize-your-star-icons/'},
                    {text: 'Customize your star CSS', link: '/customize-your-star-css/'},
                    {text: 'Sort by highest rated', link: '/sort-by-highest-rated/'},
                    {text: 'Multiple ratings for the same element', link: '/multiple-ratings-for-the-same-element/'},
                    {text: 'Prevent rating for miscellaneous reasons', link: '/prevent-rating-for-miscellaneous-reasons/'},
                    {text: '"Rate" field type', link: '/rate-field-type/'},
                    {text: '"Average User Rating" field type', link: '/average-user-rating-field-type/'},
                    {text: 'GraphQL Support', link: '/graphql-support/'},
                    {text: 'Disable JS or CSS', link: '/disable-js-or-css/'},
                    {text: 'Manually load JS or CSS assets', link: '/manually-load-js-or-css-assets/'},
                    {text: 'Get numerical value of stars', link: '/get-numerical-value-of-stars/'},
                    {text: 'Get total count of ratings cast', link: '/get-total-count-of-ratings-cast/'},
                    {text: 'Get rating cast by a specific user', link: '/get-rating-cast-by-a-specific-user/'},
                    {text: 'Cast a rating on behalf of a specific user', link: '/cast-a-rating-on-behalf-of-a-specific-user/'},
                    {text: 'Output a set of locked stars', link: '/output-a-set-of-locked-stars/'},
                    {text: 'Override star icons with CSS', link: '/override-star-icons-with-css/'},
                    {text: 'Override settings', link: '/override-settings/'},
                    {text: 'Events', link: '/events/'},
                    {text: 'BREAKING CHANGE (v2.0.0)', link: '/breaking-change-v2-0-0/'},
                ]
            },
            {
                text: 'More',
                items: [
                    {text: 'Double Secret Agency', link: 'https://www.doublesecretagency.com/plugins'},
                    {text: 'Our other Craft plugins', link: 'https://plugins.doublesecretagency.com', target:'_self'},
                ]
            },
        ],
        sidebar: {
            '/': [
                'getting-started',
                'display-star-ratings',
                'customize-your-star-icons',
                'customize-your-star-css',
                'sort-by-highest-rated',
                'multiple-ratings-for-the-same-element',
                'prevent-rating-for-miscellaneous-reasons',
                'rate-field-type',
                'average-user-rating-field-type',
                'graphql-support',
                'disable-js-or-css',
                'manually-load-js-or-css-assets',
                'get-numerical-value-of-stars',
                'get-total-count-of-ratings-cast',
                'get-rating-cast-by-a-specific-user',
                'cast-a-rating-on-behalf-of-a-specific-user',
                'output-a-set-of-locked-stars',
                'override-star-icons-with-css',
                'override-settings',
                'events',
                'breaking-change-v2-0-0',
            ],
        }
    }
};
