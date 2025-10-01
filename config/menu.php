<?php
return [
    'left-menu-admin'   => [
        [
            'name'      => 'Đơn hàng',
            'route'     => '',
            'icon'      => '<i class="fa-regular fa-file-lines"></i>'
        ],
        [
            'name'  => 'Danh mục',
            'route' => 'admin.category.list',
            'icon'  => '<i class="fa-solid fa-table-list"></i>',
        ],
        [
            'name'  => 'Sản phẩm',
            'route' => '',
            'icon'  => '<i class="fa-solid fa-handshake"></i>',
            'child'     => [
                [
                    'name'  => '1. Danh sách',
                    'route' => 'admin.product.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                [
                    'name'  => '2. Tags',
                    'route' => 'admin.tag.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
            ]
        ],
        [
            'name'      => 'Trang',
            'route'     => 'admin.page.list',
            'icon'      => '<i class="fa-regular fa-file-lines"></i>',
            // 'child'     => [
            //     [
            //         'name'  => '1. Danh sách',
            //         'route' => '',
            //         'icon'  => '<i data-feather=\'circle\'></i>'
            //     ]
            // ]
        ],
        [
            'name'      => 'Blog',
            'route'     => '',
            'icon'      => '<i class="fa-solid fa-blog"></i>',
            'child'     => [
                [
                    'name'  => '1. Chuyên mục',
                    'route' => 'admin.categoryBlog.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                [
                    'name'  => '2. Bài viết',
                    'route' => 'admin.blog.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                
            ]
        ],
        [
            'name'      => 'Ảnh',
            'route'     => 'admin.image.list',
            'icon'      => '<i class="fa-regular fa-images"></i>',
        ],
        // [
        //     'name'      => 'Cài đặt',
        //     'route'     => '',
        //     'icon'      => '<i class="fa-solid fa-gear"></i>',
        //     'child'     => [
        //         [
        //             'name'  => '1. Giao diện',
        //             'route' => 'admin.theme.list',
        //             'icon'  => '<i data-feather=\'circle\'></i>'
        //         ],
        //         [
        //             'name'  => '2. Slider home',
        //             'route' => 'admin.setting.slider',
        //             'icon'  => '<i data-feather=\'circle\'></i>'
        //         ]
        //     ]
        // ],
        [
            'name'      => 'Công cụ SEO',
            'route'     => '',
            'icon'      => '<i class="fa-solid fa-screwdriver-wrench"></i>',
            'child'     => [
                [
                    'name'  => '1. Redirect 301',
                    'route' => 'admin.redirect.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
            ]
        ],
        [
            'name'      => 'Công nghệ AI',
            'route'     => '',
            'icon'      => '<i class="fa-solid fa-robot"></i>',
            'child'     => [
                [
                    'name'  => '1. Prompt',
                    'route' => 'admin.prompt.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                [
                    'name'  => '2. API AI',
                    'route' => 'admin.apiai.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
            ]
        ],
        [
            'name'      => 'Báo cáo',
            'route'     => '',
            'icon'      => '<i class="fa-solid fa-flag-checkered"></i>',
            'child'     => [
                [
                    'name'  => '1. Auto dịch',
                    'route' => 'admin.translate.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                [
                    'name'  => '2. Check translate page',
                    'route' => 'admin.checkTranslateOfPage.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
            ]
        ],
    ]
];