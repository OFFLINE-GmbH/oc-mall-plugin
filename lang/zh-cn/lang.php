<?php

return [
    'plugin' => [
        'name' => '购物中心',
        'description' => 'OctoberCMS的电子商务解决方案',
    ],
    'titles' => [
        'products' => [
            'create' => '创建产品',
            'update' => '编辑产品',
            'preview' => '预览产品',
        ],
        'categories' => [
            'create' => '创建类别',
            'update' => '编辑类别',
            'preview' => '预览类别',
        ],
        'orders' => [
            'show' => '订单详细信息',
            'export' => '导出订单',
        ],
        'discounts' => [
            'create' => '创建折扣',
            'update' => '编辑折扣',
            'preview' => '预览折扣',
        ],
        'services' => [
            'create' => '创建服务',
            'update' => '编辑服务',
        ],
        'shipping_methods' => [
            'create' => '创建运输方式',
            'update' => '编辑运输方式',
            'preview' => '预览运输方式',
        ],
        'payment_methods' => [
            'create' => '创建付款方式',
            'edit' => '编辑付款方式',
            'reorder' => '重新排序',
        ],
        'custom_field_options' => [
            'edit' => '编辑字段选项',
        ],
        'properties' => [
            'create' => '创建属性',
            'edit' => '编辑属性',
        ],
        'order_states' => [
            'create' => '创建状态',
            'edit' => '编辑状态',
            'reorder' => '重新订购状态',
        ],
        'brands' => [
            'create' => '创建品牌',
            'edit' => '编辑品牌',
        ],
        'property_groups' => [
            'create' => '创建组',
            'edit' => '编辑组',
        ],
        'customer_groups' => [
            'create' => '创建组',
            'update' => '编辑组',
        ],
        'notifications' => [
            'update' => '更新通知',
        ],
        'taxes' => [
            'create' => '创建税',
            'update' => '编辑税',
        ],
        'reviews' => [
            'create' => '创建评论',
            'update' => '编辑评论',
        ],
    ],
    'menu_items' => [
        'all_categories' => '所有商店类别',
        'single_category' => '类别',
        'all_products' => '全部产品',
        'all_variants' => '所有属性',
    ],
    'currency_settings' => [
        'label' => '货币',
        'description' => '设置您的货币',
        'currencies' => '只输入官方的 3 字符货币代码。',
        'currency_code' => '货币代码',
        'currency_decimals' => '小数位',
        'currency_rounding' => '将总数四舍五入',
        'currency_rounding_comment' => '如果此货币处于活动状态，则总计(包括税费)将四舍五入到此值。',
        'currency_format' => '格式',
        'currency_symbol' => '符号',
        'currency_rate' => '货币利率',
        'is_default' => '默认',
        'unknown' => '未知货币',
    ],
    'payment_gateway_settings' => [
        'label' => '支付网关',
        'description' => '配置您的支付网关',
        'stripe' => [
            'api_key' => 'Stripe API密钥',
            'api_key_comment' => '你可以在你的 Stripe Dashboard 中找到这个key',
            'publishable_key' => 'Stripe可发布密钥',
            'publishable_key_comment' => '你可以在你的 Stripe Dashboard 中找到这个key',
        ],
        'paypal' => [
            'client_id' => 'PayPal客户 ID',
            'secret' => 'PayPal密码',
            'test_mode' => '测试模式',
            'test_mode_comment' => '在 PayPal 沙盒中运行所有付款。',
        ],
        'postfinance' => [
            'test_mode' => '测试模式',
            'test_mode_comment' => '针对测试环境运行所有付款',
            'pspid' => 'PSPID(用户名)',
            'hashing_method' => '哈希算法',
            'hashing_method_comment' => '配置 -> 技术信息 -> 全局安全参数',
            'sha_in' => 'SHA-IN 签名',
            'sha_in_comment' => '配置 -> 技术信息 -> 数据和来源验证',
            'sha_out' => 'SHA-OUT签名',
            'sha_out_comment' => '配置 -> 技术信息 -> 交易反馈',
        ],
    ],
    'notification_settings' => [
        'label' => '通知',
        'description' => '配置商店通知',
    ],
    'price_category_settings' => [
        'label' => '价格类别',
        'description' => '配置附加价格类别',
    ],
    'order_state_settings' => [
        'description' => '配置订单状态',
    ],
    'payment_method_settings' => [
        'description' => '管理付款方式',
    ],
    'shipping_method_settings' => [
        'description' => '管理运输方式',
    ],
    'tax_settings' => [
        'description' => '管理税收',
    ],
    'general_settings' => [
        'category' => '商城：常规',
        'category_payments' => '商城：付款',
        'category_orders' => '商城：订单',
        'label' => '配置',
        'description' => '通用设置',
        'product_page' => '产品详情页',
        'product_page_comment' => '这是显示产品详细信息的地方',
        'address_page' => '地址页',
        'address_page_comment' => 'addressForm 组件必须出现在此页面上',
        'checkout_page' => '结帐页面',
        'checkout_page_comment' => '结帐组件必须出现在此页面上',
        'account_page' => '帐户页面',
        'account_page_comment' => 'myAccount 组件必须出现在此页面上',
        'cart_page' => '购物车页面',
        'cart_page_comment' => '购物车组件必须出现在此页面上',
        'category_page' => '产品列表的类别页面',
        'redirect_to_cart' => '重定向到购物车',
        'redirect_to_cart_comment' => '用户添加产品后重定向到购物车',
        'use_state' => '使用省市县(州/县/省)字段',
        'use_state_comment' => '客户必须在注册时选择省市县(州/县/省)',
        'group_search_results_by_product' => '按产品分组搜索结果',
        'group_search_results_by_product_comment' => '在搜索结果中仅包含一次产品，不显示所有匹配的属性',
        'shipping_selection_before_payment' => '在结帐时付款前选择送货方式',
        'shipping_selection_before_payment_comment' => '默认情况下，在结账时，会先要求用户选择付款方式，然后再选择送货方式；使用这个选项来反转这个逻辑',
        'admin_email' => '管理员电子邮件',
        'admin_email_comment' => '管理员通知将发送到此地址',
        'base' => '基本设置',
        'links' => 'CMS 页面',
        'links_comment' => '选择用于展示您的产品的页面',
        'customizations' => '定制',
        'customizations_comment' => '自定义您商店的功能',
        'category_page_comment' => '将"产品"组件添加到此页面。',
        'order_number_start' => '第一个订单号',
        'order_number_start_comment' => '第一个订单的初始id',
        'index_driver' => '索引驱动程序',
        'index_driver_comment' => '如果您的数据库支持 JSON，请使用数据库驱动程序。',
        'index_driver_filesystem' => '文件系统',
        'index_driver_database' => '数据库(仅适用于 MySQL 5.7+ 或 MariaDB 10.2+)',
        'index_driver_hint' => '如果您更改此选项，请确保在命令行上运行 :command 以重新索引您的产品！',
    ],
    'feed_settings' => [
        'description' => '配置商城提要',
        'google_merchant_enabled' => '启用 Google Merchant Center Feed',
        'google_merchant_enabled_comment' => '将生成产品摘要',
        'google_merchant_url' => '您的 Google Merchant Feed URL',
        'google_merchant_url_locale' => '添加 ?locale=xy 以获得本地化的提要。',
    ],
    'review_settings' => [
        'description' => '配置评论',
        'enabled' => '启用评论',
        'enabled_comment' => '客户可以创建评论',
        'moderated' => '审查评论',
        'moderated_comment' => '新评论必须由站点管理员手动发布',
        'allow_anonymous' => '允许匿名评论',
        'allow_anonymous_comment' => '未注册用户可以创建评论',
    ],
    'common' => [
        'shop' => '店铺',
        'products' => '产品',
        'product' => '产品',
        'orders' => '订单',
        'cart' => '购物车',
        'shipping' => '送货',
        'taxes' => '税',
        'rates' => '汇率',
        'inventory' => '库存',
        'accessories' => '配件',
        'shipping_methods' => '运输方式',
        'accessory' => '配件',
        'custom_fields' => '自定义字段',
        'variants' => '属性',
        'variant' => '属性',
        'discounts' => '折扣',
        'discount' => '折扣',
        'discount_percentage' => '折扣 (％)',
        'select_placeholder' => '--请选择',
        'main_image' => '主图',
        'images' => '图片',
        'image_set' => '图片集',
        'attachments' => '图片/下载/嵌入',
        'downloads' => '下载',
        'select_image' => '选择图像',
        'select_file' => '选择文件',
        'allowed' => '允许',
        'not_allowed' => '不允许',
        'yes' => '是',
        'no' => '否',
        'seo' => '搜索引擎优化',
        'properties_links' => '属性/链接',
        'categories' => '类别',
        'category' => '类别',
        'meta_title' => 'Meta标题',
        'meta_description' => 'Meta描述',
        'meta_keywords' => 'Meta关键字',
        'reorder' => '重新排序条目',
        'id' => 'ID',
        'created_at' => '创建于',
        'updated_at' => '更新于',
        'approved_at' => '批准于',
        'hide_published' => '隐藏已发布',
        'slug' => 'Slug',
        'name' => '名称',
        'display_name' => '显示名称',
        'group_name' => '组名字',
        'add_value' => '增加值',
        'export_orders' => '导出订单',
        'use_backend_defaults' => '使用后端设置中配置的默认值',
        'api_error' => '无法保存折扣。向 Mall API 发送更改时出错。',
        'includes_tax' => '包含税',
        'conditions' => '条件',
        'general' => '常规',
        'logo' => '商标',
        'payment_gateway' => '支付网关',
        'payment_provider' => '支付提供商',
        'payment_methods' => '支付方式',
        'payment' => '支付',
        'payments' => '付款',
        'image' => '图片',
        'color' => '颜色',
        'unit' => '单位',
        'dont_group' => '-- 不要分组',
        'properties' => '属性',
        'old_price' => '旧价格',
        'property' => '属性',
        'property_groups' => '属性组',
        'property_group' => '属性组',
        'options' => '选项',
        'option' => '选项',
        'catalogue' => '商城',
        'out_of_stock' => '此产品缺货。',
        'out_of_stock_short' => '缺货',
        'stock_limit_reached' => '由于已达到库存限制，您无法再将该产品的任何商品添加到您的购物车中。',
        'deleted_at' => '删除于',
        'sort_order' => '排序',
        'order_states' => '订单状态',
        'website' => '网站',
        'brands' => '品牌',
        'brand' => '品牌',
        'sorting_updated' => '排序顺序已更新',
        'not_in_use' => '选项未使用',
        'saved_changes' => '已成功保存更改',
        'none' => '--无',
        'customers' => '顾客',
        'customer_groups' => '客户群',
        'customer_group' => '客户群',
        'product_or_variant' => '产品/属性',
        'code' => '代码',
        'code_comment' => '此代码可用于以编程方式识别此记录',
        'checked' => '已检查',
        'unchecked' => '未选中',
        'notifications' => '通知',
        'notification' => '通知',
        'price_missing' => '至少为默认货币输入一个价格',
        'slug_unique' => 'URL 必须是唯一的',
        'fees' => '费用',
        'value' => '值',
        'action_required' => '需要采取的行动！？？？',
        'invalid_quantity' => '指定数量无效',
        'addresses' => '地址',
        'address' => '地址',
        'reference' => '参考',
        'session_id' => '会话 ID',
        'message' => '信息',
        'payment_method' => '付款方法',
        'data' => '数据',
        'successful' => '成功',
        'failed' => '失败',
        'caution' => '警告',
        'since_begin' => '从开始',
        'weekly' => '每周',
        'feeds' => '提要',
        'services' => '服务',
        'service' => '服务',
        'review' => '评论',
        'reviews' => '评论',
        'review_categories' => '评论类别',
        'review_category' => '评论类别',
        'title' => '标题',
        'version' => '版本',
    ],
    'variant' => [
        'method' => [
            'single' => '产品',
            'variant' => '产品属性',
        ],
    ],
    'properties' => [
        'use_for_variants' => '用于变体属性',
        'use_for_variants_comment' => '此属性因该产品的不同变体而异',
        'filter_type' => '过滤器类型',
        'filter_types' => [
            'none' => '没有过滤器',
            'set' => '集合',
            'range' => '范围',
        ],
    ],
    'custom_field_options' => [
        'text' => '文本域',
        'integer' => '整数框',
        'float' => '浮点框',
        'textarea' => '多行文本框',
        'richeditor' => '富文本框',
        'dropdown' => '下拉框',
        'checkbox' => '复选框',
        'color' => '颜色',
        'image' => '图片',
        'switch' => '开关',
        'add' => '添加选项',
        'name' => '名称',
        'price' => '价钱',
        'attributes' => '属性',
        'option' => '选项',
        'date' => '日期',
        'datetime' => '日期时间',
    ],
    'product' => [
        'user_defined_id' => '产品编号',
        'name' => '产品名称',
        'published' => '发布',
        'published_short' => '发布',
        'is_virtual' => '这是虚拟产品',
        'is_virtual_comment' => '该产品是虚拟的(一个文件，不发货)',
        'product_file' => '产品档案',
        'product_files' => '产品档案',
        'product_files_section_comment' => '这是一个虚拟产品。您可以在下面上传新的文件版本。客户可以下载最新版本。',
        'product_file_version' => '文件版本',
        'not_published' => '未发布',
        'published_comment' => '该产品在网站上可见',
        'stock' => '库存',
        'price' => '价钱',
        'description_short' => '简短的介绍',
        'description' => '描述',
        'weight' => '重量',
        'length' => '长度',
        'height' => '高度',
        'width' => '宽度',
        'quantity_default' => '默认数量',
        'quantity_min' => '最小用量',
        'quantity_max' => '最大数量',
        'inventory_management_method' => '库存管理方法',
        'allow_out_of_stock_purchases' => '允许缺货购买',
        'allow_out_of_stock_purchases_comment' => '此产品即使缺货也可以订购',
        'stackable' => '堆叠在购物车中',
        'stackable_comment' => '如果该产品多次加入购物车仅显示一个条目(增加数量)',
        'shippable' => '可发货',
        'shippable_comment' => '本产品可以发货',
        'taxable' => '应税',
        'taxable_comment' => '计算此产品的税费',
        'add_currency' => '添加货币',
        'is_taxable' => '使用税',
        'is_not_taxable' => '不使用税',
        'currency' => '货币',
        'general' => '常规',
        'duplicate_currency' => '您为同一种货币输入了多个价格',
        'property_title' => '标题',
        'property_value' => '值',
        'link_title' => '标题',
        'link_target' => '目标网址',
        'embed_title' => '标题',
        'embed_code' => '嵌入代码',
        'properties' => '属性',
        'links' => '链接',
        'embeds' => '嵌入',
        'details' => '详情',
        'price_includes_tax' => '价格含税',
        'price_includes_tax_comment' => '定义的价格包括所有税费',
        'group_by_property' => '按属性分组',
        'additional_descriptions' => '附加说明',
        'additional_properties' => '附加属性',
        'gtin' => '全球贸易项目编号 (GTIN)',
        'mpn' => '制造商零件编号 (MPN)',
        'price_table_modal' => [
            'trigger' => '更改库存和价格值',
            'label' => '价格和库存',
            'title' => '价格和库存概览',
            'currency_dropdown' => '货币：',
        ],
        'missing_category' => '该产品没有与之关联的类别。请在下面选择一个类别来编辑此产品。',
        'variant_support_header' => '不支持的属性',
        'variant_support_text' => '所选类别未定义变体属性。请将库存管理方式切换为“产品”或选择其他类别。',
        'filter_virtual' => '仅显示虚拟产品',
    ],
    'product_file' => [
        'display_name_comment' => '此名称将对客户可见。',
        'version_comment' => '唯一版本可帮助客户识别更新的文件。',
        'expires_after_days' => '下载有效天',
        'expires_after_days_comment' => '该文件只能在购买后的这几天内下载。留空无限制。',
        'max_download_count' => '最大下载次数',
        'max_download_count_comment' => '该文件只能下载这么多次。留空无限制。',
        'session_required' => '需要登录',
        'session_required_comment' => '该文件只有在客户登录后才能下载(下载链接不可共享)。',
        'file' => '文件',
        'download_count' => '下载次数',
        'errors' => [
            'invalid' => '下载链接无效',
            'expired' => '下载链接已过期',
            'too_many_attempts' => '下载尝试次数过多',
            'not_found' => '找不到请求的文件，请联系我们以获得支持。',
        ],
        'hint' => [
            'intro' => '该产品没有附加文件。请确保在结帐时添加一个或以编程方式生成它。',
            'info_text' => '您可以找到有关如何执行此操作的信息',
            'info_link' => '在文档中',
        ],
    ],
    'image_sets' => [
        'is_main_set' => '是主集',
        'is_main_set_comment' => '将此图像集用于此产品',
        'create_new' => '创建新集',
    ],
    'category' => [
        'name' => '名称',
        'code' => '代码',
        'code_comment' => '此代码可用于在您的前端部分中识别此类别。',
        'parent' => '父类',
        'no_parent' => '没有父类',
        'inherit_property_groups' => '继承父类的属性',
        'inherit_property_groups_comment' => '使用该类别的父类别的属性组',
        'inherit_review_categories' => '继承父类的评论类',
        'inherit_review_categories_comment' => '使用该分类的父分类的评论分类',
        'google_product_category_id' => 'Google 产品类别 ID',
        'google_product_category_id_comment' => '用于 Google Merchant 集成，请参阅 https://support.google.com/merchants/answer/6324436',
    ],
    'custom_fields' => [
        'name' => '字段名',
        'type' => '字段类型',
        'options' => '选项',
        'required' => '必填',
        'required_comment' => '此字段是下订单所必需的',
        'is_required' => '必选',
        'is_not_required' => '非必选',
    ],
    'tax' => [
        'percentage' => '百分',
        'countries' => '仅在运送到这些国家/地区时才征收税款',
        'countries_comment' => '如果未选择任何国家/地区，则税款适用于全球。',
        'is_default' => '默认',
        'is_default_comment' => '如果运输目的地国家尚不清楚，则使用此税',
    ],
    'discounts' => [
        'name' => '名称',
        'code' => '优惠码',
        'code_comment' => '留空生成随机码',
        'total_to_reach' => '折扣有效的最小订单总额',
        'type' => '折扣类型',
        'trigger' => '生效条件',
        'rate' => '汇率 (％)',
        'amount' => '定额，固定金额',
        'max_number_of_usages' => '最大使用次数',
        'valid_from' => '有效自',
        'expires' => '过期',
        'number_of_usages' => '使用次数',
        'shipping_description' => '替代运输方式的名称',
        'payment_method_description' => '选择付款方式',
        'shipping_price' => '替代运输方式的价格',
        'shipping_guaranteed_days_to_delivery' => '保证交货天数',
        'section_type' => '这个折扣有什么作用？',
        'section_trigger' => '这个折扣什么时候适用？',
        'types' => [
            'fixed_amount' => '定额，固定金额',
            'rate' => '汇率',
            'shipping' => '备用运输',
        ],
        'triggers' => [
            'total' => '订单总额已达到',
            'code' => '折扣码已输入',
            'product' => '购物车中有特定产品',
            'shipping_method' => '运送方式是以下之一',
            'customer_group' => '用户属于特定的客户群',
            'payment_method' => '取决于付款方式',
        ],
        'validation' => [
            'empty' => '输入促销代码',
            'shipping' => '您只能应用一个可以降低运费的促销代码。',
            'duplicate' => '您只能使用一次相同的促销代码。',
            'expired' => '此促销代码已过期。',
            'not_found' => '此促销代码无效。',
            'usage_limit_reached' => '此促销代码已多次应用，因此不再有效。',
            'cart_limit_reached' => '已达到促销代码限制。您不能再为此购物车添加促销代码。',
        ],
    ],
    'payment_method' => [
        'price' => '固定费用',
        'price_comment' => '添加到订单总金额的金额',
        'fee_percentage' => '百分比费用',
        'fee_percentage_comment' => '添加到订单总数的百分比',
        'fee_label' => '费用标签',
        'fee_label_comment' => '此文本将在结帐时显示给客户。',
        'instructions' => '付款说明',
        'instructions_comment' => '支持 Twig 语法。使用 {{ order }} 或 {{ car​​t }} 访问相应的信息(如果有)',
        'pdf_partial' => 'PDF附件部分',
        'pdf_partial_comment' => '对于使用此付款方式的所有订单，所选部分的呈现 PDF 将附加到通知邮件',
        'pdf_partial_none' => '没有 PDF 附件',
    ],
    'order' => [
        'order_number' => '# 订单',
        'invoice_number' => '# 发票',
        'payment_hash' => '支付哈希',
        'customer' => '顾客',
        'creation_date' => '创建于',
        'modification_date' => '修改于',
        'completion_date' => '完成于',
        'credit_card' => '信用卡',
        'payment_status' => '支付状态',
        'grand_total' => '累计',
        'billing_address' => '帐单地址',
        'shipping_address' => '送货地址',
        'currency' => '货币',
        'status' => '状态',
        'email' => '电子邮件',
        'will_be_paid_later' => '稍后付款',
        'shipping_address_same_as_billing' => '送货地址与帐单相同',
        'credit_card_last4_digits' => '最后 4 位数字',
        'tracking_number' => '追踪号码',
        'tracking_url' => '跟踪网址',
        'tracking_shipped' => '将订单标记为已发货',
        'tracking_shipped_comment' => '订单将被标记为已发货',
        'tracking_completed' => '将订单标记为完成',
        'tracking_completed_comment' => '订单将被标记为完成',
        'tracking_notification' => '发送通知',
        'tracking_notification_comment' => '包含跟踪信息的通知将发送给客户',
        'shipping_fees' => '运送费',
        'shipping_provider' => '运输供应商',
        'shipping_method' => '邮寄方式',
        'card_holder_name' => '持卡人',
        'card_type' => '卡的种类',
        'payment_method' => '付款方法',
        'payment_gateway_used' => '支付网关',
        'tax_provider' => '税务提供者',
        'lang' => '语言',
        'refunds_amount' => '退款金额',
        'adjusted_amount' => '调整金额',
        'rebate_amount' => '回扣金额',
        'total' => '总数',
        'taxes_total' => '税收总额',
        'items_total' => '项目总数',
        'subtotal' => '小计',
        'taxable_total' => '应税总额',
        'total_weight' => '总重量',
        'total_rebate_rate' => '总回扣率',
        'notes' => '笔记',
        'custom_fields' => '自定义字段',
        'shipping_enabled' => '发货已启用',
        'payment_transaction_id' => '支付交易id',
        'change_order_status' => '更改订单状态',
        'change_payment_status' => '更改付款状态',
        'items' => '项目',
        'quantity' => '数量',
        'shipping_address_is_same_as_billing' => '送货地址与帐单地址相同',
        'update_shipping_state' => '更新运输状态',
        'invalid_status' => '所选状态不存在。',
        'updated' => '订单更新成功',
        'deleted' => '订单已成功删除',
        'deleting' => '删除订单...',
        'delete_confirm' => '你真的要删除这个订单吗？',
        'update_invoice_number' => '设置发票编号',
        'shipped' => '已发货',
        'shipping_pending' => '发货待定',
        'not_shipped' => '待办的',
        'data' => '订单数据',
        'total_revenue' => '总收入',
        'download_invoice' => '下载发票',
        'order_file_name' => '订单-：订单',
        'virtual_product_download_hint' => '下载链接将在付款后单独发送。',
        'modal' => [
            'cancel' => '取消',
            'update' => '更新信息',
        ],
        'payment_states' => [
            'pending_state' => '付款等待中',
            'failed_state' => '支付失败',
            'refunded_state' => '已退款',
            'paid_state' => '已支付',
        ],
    ],
    'shipping_method' => [
        'guaranteed_delivery_days' => '保证在几天内交货',
        'available_above_total' => '如果总计大于或等于,则可用',
        'available_below_total' => '如果总数低于,则可用',
        'countries' => '可运送到这些国家',
        'countries_comment' => '如果未选择任何国家/地区，则此方法在全球范围内可用。',
        'not_required_name' => '无需运输',
        'not_required_description' => '当前的购物车不需要任何运输。',
    ],
    'payment_status' => [
        'paid' => '支付',
        'deferred' => '延期',
        'paid_deferred' => '支付延期',
        'paiddeferred' => '支付延期',
        'charged_back' => '退还',
        'refunded' => '退款',
        'paidout' => '支付',
        'failed' => '失败的',
        'pending' => '待办的',
        'expired' => '已到期',
        'cancelled' => '取消',
        'open' => '打开',
    ],
    'permissions' => [
        'manage_products' => '可以管理产品',
        'manage_categories' => '可以管理类别',
        'manage_orders' => '可以管理订单',
        'manage_discounts' => '可以管理折扣',
        'settings' => [
            'manage_general' => '可以更改一般商店设置',
            'manage_payment_gateways' => '可以更改支付网关设置',
            'manage_currency' => '可以更改货币商店设置',
            'manage_payment_methods' => '可以更改付款方式',
        ],
        'manage_properties' => '可以编辑产品属性',
        'manage_customer_groups' => '可以管理客户群',
        'manage_customer_addresses' => '可以管理客户地址',
        'manage_notifications' => '可以管理通知',
        'manage_price_categories' => '可以管理价格类别',
        'manage_order_states' => '可以管理订单状态',
        'manage_shipping_methods' => '可以管理运输方式',
        'manage_taxes' => '可以管理税收',
        'manage_payment_log' => '可以管理支付日志',
        'manage_feeds' => '可以管理提要',
        'manage_wishlists' => '可以管理心愿单',
        'manage_services' => '可以管理服务',
        'manage_reviews' => '可以管理评论',
        'manage_brands' => '可以管理品牌',
    ],
    'components' => [
        'products' => [
            'details' => [
                'name' => '产品',
                'description' => '显示产品列表',
            ],
            'properties' => [
                'no_category_filter' => '不要按类别过滤',
                'use_url' => '使用来自 URL 的类别 slug',
                'filter_component' => [
                    'title' => '过滤组件别名',
                    'description' => '过滤此产品组件的产品过滤器组件的别名',
                ],
                'filter' => [
                    'title' => '过滤字符串',
                    'description' => '为此组件强制过滤',
                ],
                'include_variants' => [
                    'title' => '显示文章属性',
                    'description' => '不显示单个产品，而是显示所有可用的产品属性',
                ],
                'include_children' => [
                    'title' => '包括子类',
                    'description' => '也显示子类别的所有产品',
                ],
                'per_page' => [
                    'title' => '每页',
                    'description' => '每页显示多少产品',
                ],
                'paginate' => [
                    'title' => '分页',
                    'description' => '对结果进行分页(显示多页)',
                ],
                'sort' => [
                    'title' => '种类',
                    'description' => '这会覆盖用户的排序首选项',
                ],
                'set_page_title' => [
                    'title' => '设置页面标题',
                    'description' => '使用类别名称作为页面标题',
                ],
            ],
        ],
        'productsFilter' => [
            'details' => [
                'name' => '产品过滤器',
                'description' => '从一个类别中过滤产品',
            ],
            'properties' => [
                'showPriceFilter' => [
                    'title' => '显示价格过滤器',
                ],
                'showBrandFilter' => [
                    'title' => '显示品牌过滤器',
                ],
                'showOnSaleFilter' => [
                    'title' => '显示特价过滤器',
                ],
                'includeChildren' => [
                    'title' => '包括子类',
                    'description' => '还包括子类别中产品的属性和过滤器',
                ],
                'includeVariants' => [
                    'title' => '包括属性',
                    'description' => '显示属性属性的过滤器',
                ],
                'includeSliderAssets' => [
                    'title' => '包括区域范围滑块插件noUiSlider',
                    'description' => '通过cdnjs包含noUI Slider的所有依赖项',
                ],
                'sortOrder' => [
                    'title' => '排序',
                    'description' => '初始排序顺序',
                ],
            ],
            'sortOrder' => [
                'bestseller' => '畅销产品',
                'priceLow' => '最低价格',
                'priceHigh' => '最高价',
                'latest' => '最新的',
                'oldest' => '最老的',
                'random' => '随机的',
                'manual' => '手动的',
                'name' => '名称',
                'ratings' => '评分',
            ],
        ],
        'myAccount' => [
            'details' => [
                'name' => '用户帐号',
                'description' => '显示不同的表单，用户可以在其中查看和编辑他的个人资料',
            ],
            'properties' => [
                'page' => [
                    'title' => '活动子页面',
                ],
            ],
            'pages' => [
                'orders' => '订单',
                'profile' => '简介',
                'addresses' => '地址',
            ],
        ],
        'customerProfile' => [
            'details' => [
                'name' => '客户档案',
                'description' => '显示客户资料编辑表单。',
            ],
            'properties' => [
            ],
        ],
        'currencyPicker' => [
            'details' => [
                'name' => '货币选择器',
                'description' => '显示一个选择器以选择当前活动的商店货币',
            ],
            'properties' => [
            ],
        ],
        'dependencies' => [
            'details' => [
                'name' => '前端依赖项',
                'description' => '包括所有需要的前端依赖项',
            ],
            'properties' => [
            ],
        ],
        'addressList' => [
            'details' => [
                'name' => '地址列表',
                'description' => '显示所有注册用户地址的列表',
            ],
            'properties' => [
            ],
            'errors' => [
                'address_not_found' => '找不到请求的地址',
                'cannot_delete_last_address' => '您不能删除您的最后一个地址',
            ],
            'messages' => [
                'address_deleted' => '地址已删除',
                'default_billing_address_changed' => '默认帐单地址已更改',
                'default_shipping_address_changed' => '默认送货地址已更改',
            ],
        ],
        'ordersList' => [
            'details' => [
                'name' => '订单清单',
                'description' => '显示所有客户订单的列表',
            ],
            'properties' => [
            ],
        ],
        'product' => [
            'details' => [
                'name' => '产品详情',
                'description' => '显示产品的详细信息',
            ],
            'properties' => [
                'redirectOnPropertyChange' => [
                    'title' => '重定向属性更改',
                    'description' => '如果属性已更改，则将用户重定向到新的详细信息页面',
                ],
            ],
            'added_to_cart' => '添加产品成功',
        ],
        'productReviews' => [
            'details' => [
                'name' => '产品评论',
                'description' => '显示产品的所有评论',
            ],
            'properties' => [
                'perPage' => [
                    'title' => '每页评论数',
                ],
                'currentVariantReviewsOnly' => [
                    'title' => '仅显示此属性的评分',
                    'description' => '不显示对该产品其他属性的评论',
                ],
            ],
        ],
        'cart' => [
            'details' => [
                'name' => '购物车',
                'description' => '显示购物车',
            ],
            'properties' => [
                'showDiscountApplier' => [
                    'title' => '显示折扣应用程序',
                ],
                'discountCodeLimit' => [
                    'title' => '折扣码限制',
                    'description' => '设置为 0 表示无限代码',
                ],
                'showShipping' => [
                    'title' => '显示运费',
                ],
                'showTaxes' => [
                    'title' => '显示税收',
                ],
                'showProceedToCheckoutButton' => [
                    'title' => '显示继续结帐按钮',
                ],
            ],
        ],
        'checkout' => [
            'details' => [
                'name' => '结帐',
                'description' => '处理结帐过程',
            ],
            'errors' => [
                'missing_settings' => '请选择付款和送货方式。',
            ],
            'properties' => [
                'step' => [
                    'name' => '活动结帐步骤(自动设置)',
                ],
            ],
        ],
        'quickCheckout' => [
            'details' => [
                'name' => '快速结帐',
                'description' => '单页结帐流程',
            ],
            'errors' => [
                'signup_failed' => '创建用户帐户失败。',
            ],
        ],
        'discountApplier' => [
            'details' => [
                'name' => '促销代码输入',
                'description' => '显示促销代码输入字段',
            ],
            'discount_applied' => '折扣申请成功！',
        ],
        'shippingMethodSelector' => [
            'details' => [
                'name' => '运输选择器',
                'description' => '显示所有可用运输方式的列表',
            ],
            'errors' => [
                'unavailable' => '所选的送货方式不适用于您的订单。',
            ],
        ],
        'paymentMethodSelector' => [
            'details' => [
                'name' => '付款方式选择器',
                'description' => '显示所有可用付款方式的列表',
            ],
            'errors' => [
                'unavailable' => '所选的付款方式不适用于您的订单。',
            ],
        ],
        'addressSelector' => [
            'details' => [
                'name' => '地址选择器',
                'description' => '显示所有现有用户地址的列表',
            ],
            'errors' => [
            ],
        ],
        'addressForm' => [
            'details' => [
                'name' => '地址表格',
                'description' => '显示一个表单来编辑用户的地址',
            ],
            'properties' => [
                'address' => [
                    'title' => '地址',
                ],
                'redirect' => [
                    'title' => '重定向(保存后)',
                ],
                'set' => [
                    'title' => '将此地址用作',
                ],
            ],
            'redirects' => [
                'checkout' => '结帐页面',
            ],
            'set' => [
                'billing' => '帐单地址',
                'shipping' => '送货地址',
            ],
        ],
        'signup' => [
            'details' => [
                'name' => '注册',
                'description' => '显示注册和登录表格',
            ],
            'properties' => [
                'redirect' => [
                    'name' => '登录后重定向',
                ],
            ],
            'errors' => [
                'user_is_guest' => '您正在尝试使用访客帐户登录。',
                'unknown_user' => '您输入的凭据无效。',
                'not_activated' => '您需要先激活您的帐户才能登录。',
                'login' => [
                    'required' => '请输入电子邮件地址。',
                    'email' => '请输入有效的电子邮件地址。',
                    'between' => '请输入有效的电子邮件地址。',
                ],
                'password' => [
                    'required' => '请输入您的密码。',
                    'max' => '提供的密码太长。',
                    'min' => '提供的密码太短。请输入至少 8 个字符。',
                ],
                'password_repeat' => [
                    'required' => '请重复您的密码。',
                    'same' => '您的确认密码与您输入的密码不符。',
                ],
                'email' => [
                    'required' => '请输入电子邮件地址。',
                    'email' => '此电子邮件地址无效。',
                    'unique' => '使用此电子邮件地址的用户已注册。',
                    'non_existing_user' => '使用此电子邮件地址的用户已注册。使用密码重置功能。',
                ],
                'firstname' => [
                    'required' => '请输入您的名字。',
                ],
                'lastname' => [
                    'required' => '请输入您的姓氏。',
                ],
                'lines' => [
                    'required' => '请输入您的地址。',
                ],
                'zip' => [
                    'required' => '请输入您的邮编。',
                ],
                'city' => [
                    'required' => '请输入城市。',
                ],
                'country_id' => [
                    'required' => '选择一个国家。',
                    'exists' => '提供的国家/地区无效。',
                ],
                'state_id' => [
                    'required' => '选择一个州',
                    'exists' => '所选值无效。',
                ],
                'terms_accepted' => [
                    'required' => '请接受我们的条款和条件。',
                ],
            ],
        ],
        'categories' => [
            'details' => [
                'name' => '类别',
                'description' => '列出可用类别',
            ],
            'properties' => [
                'parent' => [
                    'title' => '从类别开始',
                    'description' => '仅显示此类别的子类别',
                ],
                'categorySlug' => [
                    'title' => '分类 slug 参数',
                    'description' => '使用此参数从 url 加载父类别',
                ],
                'categoryPage' => [
                    'title' => '分类页面',
                    'description' => '链接将指向此页面。如果未输入任何内容，则将使用后端设置中的默认设置。',
                ],
            ],
            'no_parent' => '显示所有类别',
            'by_slug' => '使用 url 中的类别作为父级',
        ],
        'cartSummary' => [
            'details' => [
                'name' => '购物车摘要',
                'description' => '显示购物车中的产品数量和总价值',
            ],
            'properties' => [
                'showItemCount' => [
                    'title' => '显示产品数量',
                    'description' => '显示购物车中的商品数量',
                ],
                'showTotalPrice' => [
                    'title' => '显示总值',
                    'description' => '显示购物车中所有商品的总价值',
                ],
            ],
        ],
        'customerDashboard' => [
            'details' => [
                'name' => '客户仪表板',
                'description' => '显示客户登录和更改帐户设置的链接',
            ],
            'properties' => [
                'customerDashboardLabel' => [
                    'title' => '客户仪表板标签',
                    'description' => '客户帐户页面的链接文本',
                ],
                'logoutLabel' => [
                    'title' => '注销标签',
                    'description' => '注销链接的链接文本',
                ],
            ],
        ],
        'enhancedEcommerceAnalytics' => [
            'details' => [
                'name' => '增强型电子商务 (UA) 组件',
                'description' => '实现一个谷歌标签管理器数据层',
            ],
        ],
        'wishlistButton' => [
            'details' => [
                'name' => '心愿单按钮',
                'description' => '显示心愿单按钮',
            ],
            'properties' => [
                'product' => [
                    'name' => '产品',
                    'description' => '产品编号',
                ],
                'variant' => [
                    'name' => '属性',
                    'description' => '属性的 ID',
                ],
            ],
        ],
        'wishlists' => [
            'details' => [
                'name' => '心愿单',
                'description' => '显示心愿单管理器',
            ],
            'properties' => [
                'showShipping' => [
                    'name' => '显示运费',
                    'description' => '显示运费和选择器',
                ],
            ],
        ],
    ],
    'shipping_method_rates' => [
        'from_weight' => '从(克重)',
        'to_weight' => '至(克重)',
    ],
    'products' => [
        'variants_comment' => '创建同一产品的不同属性',
    ],
    'order_states' => [
        'name' => '名称',
        'description' => '描述',
        'color' => '颜色',
        'flag' => '特殊标志',
        'flags' => [
            'new' => '将订单状态设置为“新”',
            'complete' => '将订单状态设置为“完成”',
            'cancelled' => '将订单状态设置为“已取消”',
        ],
    ],
    'customer_group' => [
        'code_comment' => '此代码可用于以编程方式识别该组',
        'discount_comment' => '在您的整个目录中为该客户群提供特定的折扣百分比',
    ],
    'order_status' => [
        'processed' => '处理',
        'disputed' => '争议',
        'shipped' => '已发货',
        'delivered' => '已交付',
        'pending' => '待办',
        'cancelled' => '取消',
    ],
    'notifications' => [
        'enabled' => '启用',
        'enabled_comment' => '此通知已启用',
        'template' => '邮件模板',
    ],
    'payment_log' => [
        'payment_data' => '支付数据',
        'data_comment' => '此数据已由支付提供商返回',
        'order_data_comment' => '这是此付款的所有订单数据',
        'message_comment' => '此消息已由支付提供商返回',
        'code_comment' => '此代码已由支付提供商返回',
        'failed_only' => '仅失败',
    ],
    'services' => [
        'options' => '选项',
        'option' => '选项',
        'required' => '需要服务',
        'required_comment' => '将产品添加到购物车时，必须选择此服务的一个选项。',
    ],
    'reviews' => [
        'rating' => '评分',
        'review' => '查看详细信息',
        'title' => '您的评论标题',
        'pros' => '好评',
        'cons' => '差评',
        'anonymous' => '匿名',
        'only_unapproved' => '只显示未批准的',
        'no_more' => '不再有未经批准的评论',
        'approved' => '审核通过',
        'approve' => '批准审查',
        'approve_next' => '批准并转到下一步',
    ],
];
