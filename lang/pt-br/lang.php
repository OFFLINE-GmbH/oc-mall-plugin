<?php
return [
    "category" => [
        "code" => "Código",
        "code_comment" => "Este código pode ser usado para identificar esta categoria nos partials do frontend.",
        "google_product_category_id" => "ID da Categoria do Produto no Google",
        "google_product_category_id_comment" => "Usado para o Google Merchant integration, veja https://support.google.com/merchants/answer/6324436",
        "inherit_property_groups" => "Herdar propriedades da categoria pai",
        "inherit_property_groups_comment" => "Usar a propriedade grupos da categoria pai",
        "inherit_review_categories" => "Herdar categorias de avaliações da categoria pai",
        "inherit_review_categories_comment" => "Usar a recomendação de categorias da categoria pai",
        "name" => "Nome",
        "no_parent" => "Sem pai",
        "parent" => "Pai"
    ],
    "common" => [
        "accessories" => "Acessórios",
        "accessory" => "Acessório",
        "action_required" => "Ação obrigatória!",
        "add_value" => "Adicionar valor",
        "address" => "Endereço",
        "addresses" => "Endereços",
        "allowed" => "Permitido",
        "api_error" => "Não foi possível salvar o desconto. Erro ao enviar alterações para o Mall API.",
        "approved_at" => "Aprovado em",
        "attachments" => "Imagens/Downloads/Embeds",
        "brand" => "Marca",
        "brands" => "Marcas",
        "cart" => "Carrinho",
        "catalogue" => "Catálogo",
        "categories" => "Categorias",
        "category" => "Categoria",
        "caution" => "Atenção",
        "checked" => "Ativo",
        "code" => "Código",
        "code_comment" => "Este código será usado para identificar este registro na programação",
        "color" => "Cor",
        "conditions" => "Condições",
        "created_at" => "Criado em",
        "custom_fields" => "Campos customizados",
        "customer_group" => "Grupo de cliente",
        "customer_groups" => "Grupos de clientes",
        "customers" => "Clientes",
        "data" => "Data",
        "deleted_at" => "Removido em",
        "discount" => "Desconto",
        "discount_percentage" => "Desconto (%)",
        "discounts" => "Descontos",
        "display_name" => "Nome",
        "dont_group" => "-- Não agrupar",
        "downloads" => "Downloads",
        "export_orders" => "Exportar pedidos",
        "failed" => "Falhou",
        "feeds" => "Feeds",
        "fees" => "Taxas",
        "general" => "Geral",
        "group_name" => "Nome do grupo",
        "hide_published" => "Esconder publicados",
        "id" => "ID",
        "image" => "Imagem",
        "image_set" => "Conjunto de imagens",
        "images" => "Imagens",
        "includes_tax" => "Incluindo impostos",
        "invalid_quantity" => "A quantidade não é váluda",
        "inventory" => "Inventário",
        "logo" => "Logo",
        "main_image" => "Imagem principal",
        "message" => "Mensagem",
        "meta_description" => "Meta description",
        "meta_keywords" => "Meta keywords",
        "meta_title" => "Meta title",
        "name" => "Nome",
        "no" => "Não",
        "none" => "-- Nenhum",
        "not_allowed" => "Não permitido",
        "not_in_use" => "Opção não está em uso",
        "notification" => "Notificação",
        "notifications" => "Notificações",
        "old_price" => "Preço antigo",
        "option" => "Opção",
        "options" => "Opções",
        "order_states" => "Estados do pedido",
        "orders" => "Pedidos",
        "out_of_stock" => "Este produto indisponível.",
        "out_of_stock_short" => "Sem estoque",
        "payment" => "Pagamento",
        "payment_gateway" => "Gateway de pagamento",
        "payment_method" => "Método de pagamento",
        "payment_methods" => "Método de pagamentos",
        "payment_provider" => "Provider de Pagamento",
        "payments" => "Pagamentos",
        "price_missing" => "Digite ao menos um preço para a moeda padrão",
        "product" => "Produto",
        "product_or_variant" => "Produto/Variações=",
        "products" => "Produtos",
        "properties" => "Propriedades",
        "properties_links" => "Propriedades/Links",
        "property" => "Propriedade",
        "property_group" => "Grupo de Propriedade",
        "property_groups" => "Grupo de Propriedades",
        "rates" => "Taxas",
        "reference" => "Referencias",
        "reorder" => "Reordernar registros",
        "review" => "Avaliação",
        "review_categories" => "Categorias de avaliações",
        "review_category" => "Categoria de avaliação",
        "reviews" => "Avaliações",
        "saved_changes" => "Alterações salvas",
        "select_file" => "Escolher arquivo",
        "select_image" => "Escolher imagem",
        "select_placeholder" => "-- Escolher",
        "seo" => "SEO",
        "service" => "Serviço",
        "services" => "Serviços",
        "session_id" => "ID da sessão",
        "shipping" => "Entrega",
        "shipping_methods" => "Métodos de entrega",
        "shop" => "Loja",
        "since_begin" => "Desde o início",
        "slug" => "URL",
        "slug_unique" => "A URL precisa ser única",
        "sort_order" => "Ordenar por",
        "sorting_updated" => "A ordem de classificação foi atualizada",
        "stock_limit_reached" => "You cannot add any more items of este produto to your cart since the estoque limit has been reached.",
        "successful" => "Sucesso",
        "taxes" => "Imposto",
        "title" => "Título",
        "unchecked" => "Inativo",
        "unit" => "Unidade",
        "updated_at" => "Atualizado em",
        "use_backend_defaults" => "Usar padrões configurados no backend",
        "value" => "Valor",
        "variant" => "Variação",
        "variants" => "Variações",
        "version" => "Versão",
        "website" => "Site",
        "weekly" => "Semanalmente",
        "yes" => "Sim"
    ],
    "components" => [
        "addressForm" => [
            "details" => [
                "description" => "Exibe um formulário para alterar o endereço do cliente",
                "name" => "Formulário de endereço"
            ],
            "properties" => [
                "address" => ["title" => "Endereço"],
                "redirect" => ["title" => "Redirecionar (após salvar)"],
                "set" => ["title" => "Usar este endereço como"]
            ],
            "redirects" => ["checkout" => "Página de pagamento"],
            "set" => ["billing" => "Endereço de cobrança", "shipping" => "Endereço de entrega"]
        ],
        "addressList" => [
            "details" => [
                "description" => "Exibe uma lista com todos os endereços do cliente",
                "name" => "Lista de endereço"
            ],
            "errors" => [
                "address_not_found" => "O endereço não pode ser encontrado",
                "cannot_delete_last_address" => "Você não pode remover seu último endereço"
            ],
            "messages" => [
                "address_deleted" => "Endereço removido",
                "default_billing_address_changed" => "Endereço de cobrança padrão foi alterado",
                "default_shipping_address_changed" => "Endereço de entrega padrão foi alterado"
            ]
        ],
        "addressSelector" => [
            "details" => [
                "description" => "Exibe uma lista com todos os endereços do cliente",
                "name" => "Seletor de endereço"
            ]
        ],
        "cart" => [
            "details" => ["description" => "Exibe o carrinho", "name" => "Carrinho"],
            "properties" => [
                "discountCodeLimit" => [
                    "description" => "Deixe como 0 para cupons ilimitados",
                    "title" => "Limite de cupom de desconto"
                ],
                "showDiscountApplier" => ["title" => "Mostrar desconto aplicado"],
                "showProceedToCheckoutButton" => ["title" => "Mostrar botão para efetuar compra"],
                "showShipping" => ["title" => "Mostrar valor da entrega"],
                "showTaxes" => ["title" => "Mostrar impostos"]
            ]
        ],
        "cartSummary" => [
            "details" => [
                "description" => "Exibe o número de produtos e o valor total do carrinho",
                "name" => "Resumo do carrinho"
            ],
            "properties" => [
                "showItemCount" => [
                    "description" => "Exibe o total de produtos no carrinho",
                    "title" => "Mostrar total de produtos"
                ],
                "showTotalPrice" => [
                    "description" => "Exibe o valor total do carrinho",
                    "title" => "Mostrar valor total"
                ]
            ]
        ],
        "categories" => [
            "by_slug" => "Use categoria na url como pai",
            "details" => ["description" => "Listar categorias disponíveis", "name" => "Categorias"],
            "no_parent" => "Mostrar todas categorias",
            "properties" => [
                "categoryPage" => [
                    "description" => "Links vão apontar para esta página. Se nada for inserido as configurações padrão serão utilizadas.",
                    "title" => "Página de Categoria"
                ],
                "categorySlug" => [
                    "description" => "Use este parâmetro para carregar a categoria pai da url",
                    "title" => "Categoria slug"
                ],
                "parent" => [
                    "description" => "Mostrar somente categorias filhas desta categoria",
                    "title" => "Começar com a categoria"
                ]
            ]
        ],
        "checkout" => [
            "details" => ["description" => "Cuida do processo de compra", "name" => "Finalizar compra"],
            "errors" => [
                "missing_settings" => "Por favor selecione uma forma de pagamento e um método de entrega."
            ],
            "properties" => ["step" => ["name" => ""]]
        ],
        "currencyPicker" => [
            "details" => [
                "description" => "Mostra um seletor de moedas na loja",
                "name" => "Seletor de moeda"
            ]
        ],
        "customerDashboard" => [
            "details" => [
                "description" => "Exibe um link para o usuário entrar e alterar suas configurações",
                "name" => "Painel do usuário"
            ],
            "properties" => [
                "customerDashboardLabel" => [
                    "description" => "Texto para o link do painel do usuário",
                    "title" => "Nome do painel do usuário"
                ],
                "logoutLabel" => ["description" => "Texto do link para sair da conta", "title" => "Texto para sair"]
            ]
        ],
        "customerProfile" => [
            "details" => [
                "description" => "Exibe o formulário de edição do perfil do cliente.",
                "name" => "Perfil do cliente"
            ]
        ],
        "dependencies" => [
            "details" => [
                "description" => "Inclui todas as dependências necessárias do frontend",
                "name" => "Dependências do frontend"
            ]
        ],
        "discountApplier" => [
            "details" => [
                "description" => "Exibe um campo para o cupom de desconto",
                "name" => "Campo de cuspom de desconto"
            ],
            "discount_applied" => "Desconto aplicado!"
        ],
        "enhancedEcommerceAnalytics" => [
            "details" => [
                "description" => "Implements a Google Tag Manager Data Layer",
                "name" => "Enhanced Ecommerce (UA) Component"
            ]
        ],
        "myAccount" => [
            "details" => [
                "description" => "Exibe diversos formulários para o cliente gerenciar seus dados",
                "name" => "Conta"
            ],
            "pages" => ["addresses" => "Endereços", "orders" => "Pedidos", "profile" => "Perfil"],
            "properties" => ["page" => ["title" => "Sub-página ativa"]]
        ],
        "ordersList" => [
            "details" => [
                "description" => "Exibe uma lista com todos os pedidos do cliente",
                "name" => "Lista de Pedidos"
            ]
        ],
        "paymentMethodSelector" => [
            "details" => [
                "description" => "Exibe uma lista com todos os métodos de pagamentos disponíveis",
                "name" => "Seletor de Método de pagamento"
            ],
            "errors" => ["unavailable" => "O método de pagamento não é válido para o seu pedido."]
        ],
        "product" => [
            "added_to_cart" => "Produto adicionado",
            "details" => ["description" => "Exibe detalhes de um produto", "name" => "Detalhes do Produto"],
            "properties" => [
                "redirectOnPropertyChange" => [
                    "description" => "Redireciona o cliente para uma nova página de detalhes se uma propriedade for alterada",
                    "title" => "Redirecionar quando uma propriedade for alterada"
                ]
            ]
        ],
        "productReviews" => [
            "details" => [
                "description" => "Exibe todas as avaliações de um produto",
                "name" => "Avaliações do Produto"
            ],
            "properties" => [
                "currentVariantReviewsOnly" => [
                    "description" => "Não mostrar avaliações de outras variações deste produto",
                    "title" => "Mostrar somente avaliações deste Variação"
                ],
                "perPage" => ["title" => "Número de avaliações por página"]
            ]
        ],
        "products" => [
            "details" => ["description" => "Exibe uma lista de produtos", "name" => "Produtos"],
            "properties" => [
                "filter" => [
                    "description" => "Filtro forçado para este componente",
                    "title" => "Texto do filtro"
                ],
                "filter_component" => [
                    "description" => "Apelido para o componente ProdutosFilter que filtra o componente de Produtos",
                    "title" => "Apelido do componente de filtro"
                ],
                "include_children" => [
                    "description" => "Mostrar todos os produtos filtros das categorias",
                    "title" => "Incluir filhos"
                ],
                "include_variants" => [
                    "description" => "Não mostrar somente um produto mas todas as variações",
                    "title" => "Mostrar variações"
                ],
                "no_category_filter" => "Não filtrar por categoria",
                "paginate" => [
                    "description" => "Paginar o resultado (mostrar mais de uma página)",
                    "title" => "Paginar"
                ],
                "per_page" => [
                    "description" => "Quantos produtos serão mostrados por página",
                    "title" => "Por página"
                ],
                "set_page_title" => [
                    "description" => "Usa o nome da categoria como o título da página",
                    "title" => "Título da página"
                ],
                "sort" => [
                    "description" => "Isso sobrescreve a preferencia de ordenação do cliente",
                    "title" => "Ordenar"
                ],
                "use_url" => "Usar slug da categoria através da URL"
            ]
        ],
        "productsFilter" => [
            "details" => [
                "description" => "Filtra os produtos da categoria",
                "name" => "Filtro de Produtos"
            ],
            "properties" => [
                "includeChildren" => [
                    "description" => "Inclui propriedades e filtros dos produtos nas categorias filhas",
                    "title" => "Incluir filhos"
                ],
                "includeSliderAssets" => [
                    "description" => "Incluir todas as dependencias do noUI Slider via cdnjs",
                    "title" => "Incluir noUI Slider"
                ],
                "includeVariants" => [
                    "description" => "Mostrar filtro das propriedades das variações",
                    "title" => "Incluir variações"
                ],
                "showBrandFilter" => ["title" => "Mostrar filtro de marca"],
                "showOnSaleFilter" => ["title" => "Mostrar filtro de mais vendidos"],
                "showPriceFilter" => ["title" => "Mostrar filtro de preço"],
                "sortOrder" => ["description" => "Ordenação inicial", "title" => "Ordenar por"]
            ],
            "sortOrder" => [
                "bestseller" => "Mais vendidos",
                "latest" => "Mais novos",
                "manual" => "Manual",
                "name" => "Nome",
                "oldest" => "Mais antigo",
                "priceHigh" => "Maior preço",
                "priceLow" => "Menor preço",
                "random" => "Aleatório",
                "ratings" => "Avaliações"
            ]
        ],
        "quickCheckout" => [
            "details" => ["description" => "Compra em uma única página", "name" => "Compra rápida"],
            "errors" => ["signup_failed" => "Falhou ao criar conta."]
        ],
        "shippingMethodSelector" => [
            "details" => [
                "description" => "Exibe uma lista com todos métodos de entregas disponíveis",
                "name" => "Seletor de frete"
            ],
            "errors" => [
                "unavailable" => "O método de entrega selecionado não é válido para o seu pedido."
            ]
        ],
        "signup" => [
            "details" => ["description" => "Exibe o formulário de cadastro", "name" => "Cadastrp"],
            "errors" => [
                "city" => ["required" => "Por favor digite a cidade."],
                "country_id" => ["exists" => "O país informado é inválido.", "required" => "Escolha um país."],
                "email" => [
                    "email" => "Email inválido.",
                    "non_existing_user" => "Já existe um usuário cadastrado com este email. Use a opção \"Esqueceu sua senha?\"",
                    "required" => "Por favor digite um endereço de email.",
                    "unique" => "Já existe um usuário cadastrado com este email."
                ],
                "firstname" => ["required" => "Por favor digite seu nome."],
                "lastname" => ["required" => "Por favor digite seu sobrenome."],
                "lines" => ["required" => "Por favor digite seu endereço."],
                "login" => [
                    "between" => "Por favor digite um endereço de email válido.",
                    "email" => "Por favor digite um endereço de email válido.",
                    "required" => "Por favor digite um endereço de email."
                ],
                "not_activated" => "Sua conta precisa ser ativada antes de entrar.",
                "password" => [
                    "max" => "A senha digitada é muito longa.",
                    "min" => "A senha digitada é muito curta. Por favor digite ao menos 8 caracteres.",
                    "required" => "Por favor digite sua senha."
                ],
                "password_repeat" => [
                    "required" => "Por favor repita sua senha.",
                    "same" => "Sua confirmação de senha não é igual a senha digitada."
                ],
                "state_id" => ["exists" => "O valor selecionado é inválido.", "required" => "Escolha um estado"],
                "terms_accepted" => ["required" => "Por favor aceite os temos e condições."],
                "unknown_user" => "Dados inseridos são inválidos.",
                "user_is_guest" => "Você esta tentando entrar com uma conta de visitante.",
                "zip" => ["required" => "Por favor digite seu CEP."]
            ],
            "properties" => ["redirect" => ["name" => "Redireciona após o login"]]
        ],
        "wishlistButton" => [
            "details" => [
                "description" => "Exibe um botão para as listas de desejos",
                "name" => "Botão para lista de desejo"
            ],
            "properties" => [
                "product" => ["description" => "ID do produto", "name" => "Produto"],
                "variant" => ["description" => "ID da variação", "name" => "Variação"]
            ]
        ],
        "wishlists" => [
            "details" => ["description" => "Exibe o gerenciador de listas", "name" => "Lista de desejos"],
            "properties" => [
                "showShipping" => ["description" => "Mostrar seletor de frete", "name" => "Mostrar frete"]
            ]
        ]
    ],
    "currency_settings" => [
        "currencies" => "Digite somente os 3 caracteres oficiais da moeda.",
        "currency_code" => "Código da Moeda",
        "currency_decimals" => "Casas decimais",
        "currency_format" => "Formato",
        "currency_rate" => "Taxa",
        "currency_rounding" => "Arredondar total",
        "currency_rounding_comment" => "O total, includindo impostos, é arredondado para este valor caso a moeda esteja ativa.",
        "currency_symbol" => "Símbolo",
        "description" => "Configure suas moedas",
        "is_default" => "É padrão?",
        "label" => "Moedas",
        "unknown" => "Moeda desconhecida"
    ],
    "custom_field_options" => [
        "add" => "Adicionar opção",
        "attributes" => "Atributo",
        "checkbox" => "Escolha",
        "color" => "Cor",
        "date" => "Data",
        "datetime" => "Data Hora",
        "dropdown" => "Listagem",
        "float" => "Flutuante",
        "image" => "Imagem",
        "integer" => "Número",
        "name" => "Nome",
        "option" => "Opção",
        "price" => "Preço",
        "richeditor" => "Editor de texto rico",
        "switch" => "Ligado/Desligado",
        "text" => "Texto",
        "textarea" => "Editor de texto"
    ],
    "custom_fields" => [
        "is_not_required" => "Não obrigatório",
        "is_required" => "Obrigatório",
        "name" => "Nome do campo",
        "options" => "Opções",
        "required" => "Obrigatório",
        "required_comment" => "Este campo é obrigatório para fazer o pedido",
        "type" => "Tipo do campo"
    ],
    "customer_group" => [
        "code_comment" => "Este código pode ser usado para identificar esse grupo na programação",
        "discount_comment" => "Dê a esse grupo de clientes um desconto específico em % em todos os produtos"
    ],
    "discounts" => [
        "amount" => "Valor fixo",
        "code" => "Código",
        "code_comment" => "Deixe em branco para gerar um código aleatório",
        "expires" => "Expira",
        "max_number_of_usages" => "Número máximo de usos",
        "name" => "Nome",
        "number_of_usages" => "Número de usos",
        "rate" => "Taxa (%)",
        "section_trigger" => "Quando este desconto é aplicável?",
        "section_type" => "O que este desconto faz?",
        "shipping_description" => "Nome do método de entrega alternativo",
        "shipping_guaranteed_days_to_delivery" => "Dias para entrega",
        "shipping_price" => "Preço do método de entrega alternativo",
        "total_to_reach" => "Valor mínimo de pedido para aplicar o cupom",
        "trigger" => "Válido se",
        "triggers" => [
            "code" => "Código de desconto digitado",
            "customer_group" => "O cliente pertence à um grupo de clientes",
            "product" => "Produto específico esta no carrinho",
            "shipping_method" => "",
            "total" => "Valor total do pedido alcançado"
        ],
        "type" => "Tipo de desconto",
        "types" => [
            "fixed_amount" => "Valor fixo",
            "rate" => "Taxa",
            "shipping" => "Entrega diferenciada"
        ],
        "valid_from" => "Valido a partir",
        "validation" => [
            "cart_limit_reached" => "Limite de usos do cupom alcançado. Você não pode mais adicionar cupons neste carrinho.",
            "duplicate" => "Você só pode usar o cupom uma vez.",
            "empty" => "Digite o código promocional.",
            "expired" => "Este cupom expirou.",
            "not_found" => "Este cupom não é valido.",
            "shipping" => "Você só pode usar um cupom que reduz o frete por vez.",
            "usage_limit_reached" => "Este cupoem foi usado muitas vezes e não está mais disponível."
        ]
    ],
    "feed_settings" => [
        "description" => "Configurar feeds",
        "google_merchant_enabled" => "Ativar Google Merchant Center Feed",
        "google_merchant_enabled_comment" => "Um feed de produto será gerado",
        "google_merchant_url" => "Sua URL do Google Merchant Feed",
        "google_merchant_url_locale" => "Adicionar ?locale=xy para obter um feed traduzido."
    ],
    "general_settings" => [
        "account_page" => "Página da conta",
        "account_page_comment" => "O componente myAccount precisa estar presente nesta página",
        "address_page" => "Página de endereços",
        "address_page_comment" => "O componente addressForm precisa estar presente nesta página",
        "admin_email" => "E-mail do admin",
        "admin_email_comment" => "Notificações do admin serão enviadas para este email",
        "base" => "Configurações base",
        "cart_page" => "Página do Carrinho",
        "cart_page_comment" => "O componente cart precisa estar presente nesta página",
        "category" => "Mall: Geral",
        "category_orders" => "Mall: Pedidos",
        "category_page" => "Página de categoria para listar produtos",
        "category_page_comment" => "Adicionar o componente \"products\" nesta página.",
        "category_payments" => "Mall: Pagamentos",
        "checkout_page" => "Página de pagamento",
        "checkout_page_comment" => "O componente checkout precisa estar presente nesta página",
        "customizations" => "Customizações",
        "customizations_comment" => "Customize algumas funções da sua loja",
        "description" => "Configurações gerais",
        "group_search_results_by_product" => "Agrupar resultados da busca por produto",
        "group_search_results_by_product_comment" => "Incluir um produto somente uma vez nos resultados de busca, não mostrará todas as Variações",
        "index_driver" => "Index driver",
        "index_driver_comment" => "Se sua base de dados suporta JSON, selecione Database.",
        "index_driver_database" => "Database (somente para MySQL 5.7+ ou MariaDB 10.2+)",
        "index_driver_filesystem" => "Filesystem",
        "index_driver_hint" => "Se você mudar esta opção lembre-se de rodar \"php artisan mall:reindex\" na linha de comando reindexar seus produtos!",
        "label" => "Configurações",
        "links" => "CMS",
        "links_comment" => "Escolha quais páginas serão usadas para mostrar seus produtos",
        "order_number_start" => "Número do primeiro pedido",
        "order_number_start_comment" => "Código inicial do primeiro pedido",
        "product_page" => "Página de detalhes do produto",
        "product_page_comment" => "Aqui é onde os detalhes do produto serão exibidos",
        "redirect_to_cart" => "Redirecionar ao carrinho",
        "redirect_to_cart_comment" => "Redirecionar ao carrinho após o usuário adicionar um produto ao carrinho",
        "shipping_selection_before_payment" => "Selecionar método de entrega ANTES do pagamento",
        "shipping_selection_before_payment_comment" => "Por padrão, durante o pagamento, o cliente é perguntado sobre o método de pagamento antes de selecionar o método de entrega; use esta opção para reverter essa lógica",
        "use_state" => "Usar campo Estado/País/Província",
        "use_state_comment" => "Clientes precisam selecionar o estado durante o cadastro"
    ],
    "image_sets" => [
        "create_new" => "Criar novo conjunto",
        "is_main_set" => "Conjunto principal?",
        "is_main_set_comment" => "Usar este conjunto de imagens para este produto"
    ],
    "menu_items" => [
        "all_categories" => "Todas categorias",
        "all_products" => "Todos produtos",
        "all_variants" => "Todas variações",
        "single_category" => "Categoria única"
    ],
    "notification_settings" => ["description" => "Configure as notificações da loja", "label" => "Notificações"],
    "notifications" => [
        "enabled" => "Ativo",
        "enabled_comment" => "Esta notificação esta ativa",
        "template" => "Template de email"
    ],
    "order" => [
        "adjusted_amount" => "Valor ajustado",
        "billing_address" => "Endereço de cobrança",
        "card_holder_name" => "Nome no cartão",
        "card_type" => "Tipo do cartão",
        "change_order_status" => "Mudar etapa do pedido",
        "change_payment_status" => "Mudar etapa do pagamento",
        "completion_date" => "Completado em",
        "creation_date" => "Criado em",
        "credit_card" => "Cartão de Crédito",
        "credit_card_last4_digits" => "Últimos 4 digitos",
        "currency" => "Moeda",
        "custom_fields" => "Campos customizados",
        "customer" => "Cliente",
        "data" => "Dados do pedido",
        "delete_confirm" => "Você tem certeza que deseja remover este pedido?",
        "deleted" => "Pedido removido",
        "deleting" => "Removendo pedido...",
        "download_invoice" => "Baixar invoice",
        "email" => "Email",
        "grand_total" => "Total",
        "invalid_status" => "O estado selecionado não existe.",
        "invoice_number" => "# Invoice",
        "items" => "Itens",
        "items_total" => "Total dos itens",
        "lang" => "Idioma",
        "modal" => ["cancel" => "Cancelar", "update" => "Atualizar informação"],
        "modification_date" => "Modificado em",
        "not_shipped" => "Pendente",
        "notes" => "Notas",
        "order_file_name" => "pedido-:order",
        "order_number" => "# Pedido",
        "payment_gateway_used" => "Gateway de pagamento",
        "payment_hash" => "Hash do Pagamento",
        "payment_method" => "Método de pagamento",
        "payment_states" => [
            "failed_state" => "Pagamento recusado",
            "paid_state" => "Pago",
            "pending_state" => "Pagamento pendente",
            "refunded_state" => "Pagamento estornado"
        ],
        "payment_status" => "Pagamento",
        "payment_transaction_id" => "ID da transação de pagamento",
        "quantity" => "Quantidade",
        "rebate_amount" => "Valor de desconto",
        "refunds_amount" => "Total de reembolso",
        "shipped" => "Envio",
        "shipping_address" => "Endereço de entrega",
        "shipping_address_is_same_as_billing" => "Endereço de entrega é o mesmo de cobrança",
        "shipping_address_same_as_billing" => "Endereço de entrega é o mesmo de cobrança",
        "shipping_enabled" => "Frete ativo",
        "shipping_fees" => "Taxas de entrega",
        "shipping_method" => "Método de envio",
        "shipping_pending" => "Entrega pendente",
        "shipping_provider" => "Provedor de entrega",
        "status" => "Status",
        "subtotal" => "Subtotal",
        "tax_provider" => "Tax provider",
        "taxable_total" => "Total tributável",
        "taxes_total" => "Total de impostos",
        "total" => "Total",
        "total_rebate_rate" => "Total descontado",
        "total_revenue" => "Faturamento total",
        "total_weight" => "Peso total",
        "tracking_completed" => "Marcar como completo",
        "tracking_completed_comment" => "O pedido será marcado como completo",
        "tracking_notification" => "Enviar notificação",
        "tracking_notification_comment" => "A notificação com o rastreio será enviada ao cliente.",
        "tracking_number" => "Código de rastreio",
        "tracking_shipped" => "Marcar como enviado",
        "tracking_shipped_comment" => "O pedido será marcado como enviado",
        "tracking_url" => "Link de rastreio",
        "update_invoice_number" => "Número do Invoice",
        "update_shipping_state" => "Atualizar etapa da entrega",
        "updated" => "Pedido atualizado",
        "virtual_product_download_hint" => "Linkds de download serão enviados separadamente após confirmação de pagamento.",
        "will_be_paid_later" => "Vai pagar depois"
    ],
    "order_state_settings" => ["description" => "Configure os estados do pedido"],
    "order_states" => [
        "color" => "Cor",
        "description" => "Descrição",
        "flag" => "Estado especial",
        "flags" => [
            "cancelled" => "Muda o estado do pedido para \"cancelado\"",
            "complete" => "Muda o estado do pedido para \"finalizado\"",
            "new" => "Muda o estado do pedido para \"novo\""
        ],
        "name" => "Nome"
    ],
    "order_status" => [
        "cancelled" => "Cancelado",
        "delivered" => "Entregue",
        "disputed" => "Disputa",
        "pending" => "Pendente",
        "processed" => "Recebido",
        "shipped" => "Enviado"
    ],
    "payment_gateway_settings" => [
        "description" => "Configure os gateways de pagamento",
        "label" => "Gateway de pagamentos",
        "paypal" => [
            "client_id" => "PayPal Client ID",
            "secret" => "PayPal Secret",
            "test_mode" => "Modo TESTE",
            "test_mode_comment" => "Executar todos os pagamento na sandbox do PayPal."
        ],
        "postfinance" => [
            "hashing_method" => "Hash algorithm",
            "hashing_method_comment" => "Configuration -> Technical information -> Global security parameters",
            "pspid" => "PSPID (Username)",
            "sha_in" => "SHA-IN Signature",
            "sha_in_comment" => "Configuration -> Technical information -> Data and origin verification",
            "sha_out" => "SHA-OUT Signature",
            "sha_out_comment" => "Configuration -> Technical information -> Transaction feedback",
            "test_mode" => "Modo TESTE",
            "test_mode_comment" => "Executar todos os pagamento no sandbox"
        ],
        "stripe" => [
            "api_key" => "Stripe API Key",
            "api_key_comment" => "Você pode encontrar esta chave no Dashboard do Stripe",
            "publishable_key" => "Stripe Publishable Key",
            "publishable_key_comment" => "Você pode encontrar esta chave no Dashboard do Stripe"
        ]
    ],
    "payment_log" => [
        "code_comment" => "Este code foi retornado pelo provedor de pagamento",
        "data_comment" => "Estes dados foram retornados pelo provedor de pagamento",
        "failed_only" => "Somente recusados",
        "message_comment" => "Esta mensagem foi retornado pelo provedor de pagamento",
        "order_data_comment" => "Estes são todos os dados do pagamento",
        "payment_data" => "Dados de Pagamento"
    ],
    "payment_method" => [
        "fee_label" => "Nome da taxa",
        "fee_label_comment" => "Este texto irá aparecer para o cliente quando estiver pagando.",
        "fee_percentage" => "Taxa percentual",
        "fee_percentage_comment" => "O percentual do total do pedido a ser adicionado",
        "instructions" => "Instruções de Pagamento",
        "instructions_comment" => "Suporta sintaxe Twig. Use {{ order }} ou {{ cart }} para acessar informações disponíveis",
        "pdf_partial" => "Resumo em PDF",
        "pdf_partial_comment" => "Para todos pedidos com este método de pagamento um resumo em PDF será anexado no email de notificação",
        "pdf_partial_none" => "Sem PDF anexado",
        "price" => "Taxa fixa",
        "price_comment" => "O valor a ser adicionaro ao total do pedido"
    ],
    "payment_method_settings" => ["description" => "Gerenciar métodos de pagamento"],
    "payment_status" => [
        "cancelled" => "Cancelado",
        "charged_back" => "Cobrança duplicada",
        "deferred" => "Deferred",
        "expired" => "Expirou",
        "failed" => "Falhou",
        "open" => "Aberto",
        "paid" => "Pago",
        "paid_deferred" => "Paid deferred",
        "paiddeferred" => "Paid deferred",
        "paidout" => "Pago",
        "pending" => "Pendente",
        "refunded" => "Estornado"
    ],
    "permissions" => [
        "manage_brands" => "Pode gerenciar marcas",
        "manage_categories" => "Pode gerenciar categorias",
        "manage_customer_addresses" => "Pode gerenciar endereços de clientes",
        "manage_customer_groups" => "Pode gerenciar grupos de clientes",
        "manage_discounts" => "Pode gerenciar descontos",
        "manage_feeds" => "Pode gerenciar feeds",
        "manage_notifications" => "Pode gerenciar notificações",
        "manage_order_states" => "Pode gerenciar estados dos pedidos",
        "manage_orders" => "Pode gerenciar pedidos",
        "manage_payment_log" => "Pode gerenciar registros de pagamento",
        "manage_price_categories" => "Pode gerenciar categorias de preços",
        "manage_products" => "Pode gerenciar produtos",
        "manage_properties" => "Pode editar propriedades do produto",
        "manage_reviews" => "Pode gerenciar recomendações",
        "manage_services" => "Pode gerenciar serviços",
        "manage_shipping_methods" => "Pode gerenciar método de entregas",
        "manage_taxes" => "Pode gerenciar impostos",
        "manage_wishlists" => "Pode gerenciar listas",
        "settings" => [
            "manage_currency" => "Pode mudar configurações da moeda",
            "manage_general" => "Pode mudar configurações gerais da loja ",
            "manage_payment_gateways" => "Pode mudar configurações do gateway de pagamento ",
            "manage_payment_methods" => "Pode mudar método de pagamentos"
        ]
    ],
    "plugin" => ["description" => "Solução E-commerce para o October CMS", "name" => "Mall"],
    "price_category_settings" => [
        "description" => "Configure categorias de preços adicionais",
        "label" => "Categorias de Preços"
    ],
    "product" => [
        "add_currency" => "Adicionar moeda",
        "additional_descriptions" => "Descrições adicionais",
        "additional_properties" => "Propriedades adicionais",
        "allow_out_of_stock_purchases" => "Permitir comprar mesmo sem estoque",
        "allow_out_of_stock_purchases_comment" => "Este produto pode ser comprado se estiver indisponível?",
        "currency" => "Moeda",
        "description" => "Descrição",
        "description_short" => "Descrição curta",
        "details" => "Detalhes",
        "duplicate_currency" => "Você digitou vários preços para mesma moeda",
        "embed_code" => "Código",
        "embed_title" => "Título",
        "embeds" => "Embeds",
        "filter_virtual" => "Mostar somente produtos virtuais",
        "general" => "Geral",
        "group_by_property" => "Atributo para grupo de variações",
        "gtin" => "Global Trade Item Number (GTIN)",
        "height" => "Altura",
        "inventory_management_method" => "Método de gestão de estoque",
        "is_not_taxable" => "Não é tributável",
        "is_taxable" => "É tributável",
        "is_virtual" => "É virtual?",
        "is_virtual_comment" => "Este produto é virtual (ex. arquivo, sem frete)",
        "length" => "Largura",
        "link_target" => "URL Destino",
        "link_title" => "Título",
        "links" => "Links",
        "missing_category" => "O produto não tem uma categoria associada. Por favor selecione um categoria abaixo para este produto.",
        "mpn" => "Manufacturer Part Number (MPN)",
        "name" => "Produto",
        "not_published" => "Não publicado",
        "price" => "Preço",
        "price_includes_tax" => "Preço incluindo impostos",
        "price_includes_tax_comment" => "O preço definido incluindo todos os impostos",
        "price_table_modal" => [
            "currency_dropdown" => "Moeda: ",
            "label" => "Preço e estoque",
            "title" => "Preço e estoque overview",
            "trigger" => "Editar estoque e valores"
        ],
        "product_file" => "Arquivo do Produto",
        "product_file_version" => "versão do arquivo",
        "product_files" => "Arquivos do Produto",
        "product_files_section_comment" => "Este é um produto virtual. Você pode subir novas versões abaixo. A última versão sera baixada pelo cliente.",
        "properties" => "Propriedades",
        "property_title" => "Título",
        "property_value" => "Valor",
        "published" => "Ativo?",
        "published_comment" => "Este produto está visível na loja",
        "published_short" => "Publi.",
        "quantity_default" => "Quantidade padrão",
        "quantity_max" => "Quantidade máxima",
        "quantity_min" => "Quantidade mínima",
        "shippable" => "Enviável",
        "shippable_comment" => "Este produto pode ser entregue fisicamente",
        "stackable" => "Empilhar no carrinho",
        "stackable_comment" => "Se este produto é adicionado ao carrinho várias vezes aparecerá somente uma vez (aumenta a quantidade)",
        "stock" => "Estoque",
        "taxable" => "Tributável",
        "taxable_comment" => "Calcular impostos deste produto",
        "user_defined_id" => "Código do Produto",
        "variant_support_header" => "Variações não suportadas",
        "variant_support_text" => "A categoria selecionado não tem propriedades das variações definidas. Por favor alterar o médoto de gestã ode estoque para \"Artigo\" ou selecione outra categoria.",
        "weight" => "Peso",
        "width" => "Comprimento"
    ],
    "product_file" => [
        "display_name_comment" => "Este nome estará visível ao cliente.",
        "download_count" => "Qtde. Downloads",
        "errors" => [
            "expired" => "Link de download expirou",
            "invalid" => "Link de download inválido",
            "not_found" => "Arquivo não encontrado, entre em contato com o suporte.",
            "too_many_attempts" => "Muitas tentativas de download"
        ],
        "expires_after_days" => "Download valido por dias",
        "expires_after_days_comment" => "O arquivo pode ser baixado X dias após a compra. Deixar em branco para não limitar",
        "file" => "Arquivo",
        "hint" => [
            "info_link" => "na documentação",
            "info_text" => "Você pode encontrar informações sobre como fazer isso",
            "intro" => "Este produto não tem nenhum arquivo anexado. Lembre-se de adicionar um ou gerar isso durante o pagamento."
        ],
        "max_download_count" => "Número máximo de downloads",
        "max_download_count_comment" => "O arquivo pode ser baixado X vezez. Deixar em branco para não limitar",
        "session_required" => "Login obrigatório",
        "session_required_comment" => "O arquivo pode ser baixado quando o cliente está logado (link de download não pode ser compartilhado).",
        "version_comment" => "Uma versão única ajuda o cliente a reconhecer arquivos atualizados."
    ],
    "products" => ["variants_comment" => "Criar variações diferentes do mesmo produto"],
    "properties" => [
        "filter_type" => "Tipo de filtro",
        "filter_types" => ["none" => "Sem filtro", "range" => "Range", "set" => "Set"],
        "use_for_variants" => "Usar para variações",
        "use_for_variants_comment" => "Esta propriedade é diferente para variações deste produto"
    ],
    "review_settings" => [
        "allow_anonymous" => "Permitir avaliações anônimas?",
        "allow_anonymous_comment" => "Usuários não cadastrados podem criar avaliações",
        "description" => "Configurar avaliações",
        "enabled" => "Avaliações ativas",
        "enabled_comment" => "Clientes podem criar avaliações",
        "moderated" => "Moderar avaliações",
        "moderated_comment" => "Novas avaliações precisam ser publicadas manualmente"
    ],
    "reviews" => [
        "anonymous" => "Anônimo",
        "approve" => "Aprovar avaliação",
        "approve_next" => "Aprovar e ir para a próxima",
        "approved" => "Avaliação aprovada",
        "cons" => "Aspectos negativos",
        "no_more" => "Sem mais avaliações não aprovadas",
        "only_unapproved" => "Mostar somente não aprovadas",
        "pros" => "Aspectos positivos",
        "rating" => "Avaliação",
        "review" => "Revisar detalhes",
        "title" => "Título da sua avaliação"
    ],
    "services" => [
        "option" => "Opção",
        "options" => "Opções",
        "required" => "Serviço é obrigatório",
        "required_comment" => "Uma opção deste serviço precisa ser selecionada quando um produto é adicionado ao carrinho."
    ],
    "shipping_method" => [
        "available_above_total" => "Disponível se o total é maior que ou igual",
        "available_below_total" => "Disponível se o total é menor que",
        "countries" => "Disponível para entregar nestes países",
        "countries_comment" => "Se nenhum país for selecionado, este método estará disponível para o mundo todo.",
        "guaranteed_delivery_days" => "Dias para entrega garantidos",
        "not_required_description" => "O carrinho atual não precisa de frete.",
        "not_required_name" => "Sem frete"
    ],
    "shipping_method_rates" => ["from_weight" => "De (Peso em gramas)", "to_weight" => "Até (Peso em gramas)"],
    "shipping_method_settings" => ["description" => "Gerenciar métodos de entrega"],
    "tax" => [
        "countries" => "Aplicar somente nestes países",
        "countries_comment" => "Se nenhum país for selecionado, o imposto será aplicado em todo o mundo.",
        "is_default" => "É padrão?",
        "is_default_comment" => "Este imposto é usado se o país de entrega é desconhecido ainda",
        "percentage" => "Porcentagem"
    ],
    "tax_settings" => ["description" => "Gerenciar impostos"],
    "titles" => [
        "brands" => ["create" => "Criar marca", "edit" => "Editar marca"],
        "categories" => [
            "create" => "Criar categoria",
            "preview" => "Pre-visualizar Categoria",
            "update" => "Editar categoria"
        ],
        "custom_field_options" => ["edit" => "Editar opções do campo"],
        "customer_groups" => ["create" => "Criar grupo", "update" => "Editar grupo"],
        "discounts" => [
            "create" => "Criar desconto",
            "preview" => "Pre-visualizar desconto",
            "update" => "Editar desconto"
        ],
        "notifications" => ["update" => "Notificação de atualização"],
        "order_states" => [
            "create" => "Criar estado",
            "edit" => "Editar estado",
            "reorder" => "Reordernar estado"
        ],
        "orders" => ["export" => "Exportar pedidos", "show" => "Detalhes do pedido"],
        "payment_methods" => [
            "create" => "Criar método de pagamento",
            "edit" => "Editar método de pagamento",
            "reorder" => "Reordernar"
        ],
        "products" => [
            "create" => "Criar produto",
            "preview" => "Pre-visualizar produto",
            "update" => "Editar produto"
        ],
        "properties" => ["create" => "Criar propriedades", "edit" => "Editar propriedades"],
        "property_groups" => ["create" => "Criar grupo", "edit" => "Editar grupo"],
        "reviews" => ["create" => "Criar recomendação", "update" => "Editar recomendação"],
        "services" => ["create" => "Criar serviço", "update" => "Editar serviço"],
        "shipping_methods" => [
            "create" => "Criar método de entrega",
            "preview" => "Pre-visualizar método de entrega",
            "update" => "Editar método de entrega"
        ],
        "taxes" => ["create" => "Criar imposto", "update" => "Editar imposto"]
    ],
    "variant" => ["method" => ["single" => "Artigo", "variant" => "Artigo variações"]]
];
