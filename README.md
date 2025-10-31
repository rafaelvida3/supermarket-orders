# Supermarket Orders - Laravel + Vue 3

Formulário de cadastro de pedidos de supermercado com **Laravel (API REST)** no back-end e **Vue 3 + PrimeVue + Tailwind** no front-end.  

O projeto atende aos requisitos do teste (formulário de pedidos, itens com quantidades, cálculo de total em tempo real, persistência, débito de estoque, alerta de indisponibilidade e listagem do estoque atual) e documenta as **decisões de arquitetura** e **como rodar**.

## Sumário
- [Arquitetura & Decisões](#arquitetura--decisões)
- [Requisitos do Teste e Onde Estão no Código](#requisitos-do-teste-e-onde-estão-no-código)
- [Modelagem de Dados](#modelagem-de-dados)
- [API (Laravel)](#api-laravel)
- [Front-end (Vue 3)](#front-end-vue-3)
- [Validação, Erros (422) e UX](#validação-erros-422-e-ux)
- [Overlay/Loading Global](#overlayloading-global)
- [Como Rodar - Docker](#como-rodar-docker)
- [Testes](#testes)

## Arquitetura & Decisões

- **Back-end:** Laravel 12 (API stateless)  
  - Controllers: `ProductController`, `OrderController`.  
  - Rotas API em `/api` (ex.: `/api/produtos`, `/api/pedidos`).  
  - **Regras de domínio críticas no back-end** (ex.: checar estoque e debitar) - evita confiar apenas na validação do front.

- **Front-end:** Vue 3 + Vite + Vue Router + PrimeVue + TailwindCSS  
  - SPA minimalista com páginas de **Lista de Pedidos** e **Novo Pedido**.  
  - **PrimeVue** para componentes (DataTable, InputNumber, AutoComplete, Toast, ProgressSpinner).  
  - **Toast** para feedbacks; **overlay global** para carregamentos (via plugin).  
  - **Dark mode** com classes `dark:` do Tailwind aplicadas no layout base.

- **Estilo & Acessibilidade:** Tailwind para produtividade; contraste adequado no dark mode.

- **Por que estas escolhas?**  
  - A stack é popular, robusta e acelera o desenvolvimento.  
  - PrimeVue traz componentes form/UX maduros (AutoComplete, DataTable, Toast).  
  - Regras críticas (estoque, total) não ficam só no cliente.

## Requisitos do Teste e Onde Estão no Código

1. **Formulário de cadastro de pedidos** → `resources/js/components/pages/OrderPage.vue`  
2. **Nome do Cliente, Data de Entrega e lista de compras** → campos no `OrderPage.vue`  
3. **Lista = produtos + quantidade** → grid de itens com `AutoComplete` (produto) + `InputNumber` (qty)  
4. **Alterar quantidade / excluir item** → botões + binding reativo  
5. **Total recalculado a cada alteração** → feito via `watch` profundo em `items`, que atualiza `total.value` sempre que qualquer item muda (`qty` ou `price`), garantindo reatividade.
6. **Salvar tudo no banco** → `POST /api/pedidos` (Laravel migrations/models)  
7. **Debitar estoque ao salvar** → regra no `OrderController@store` (transação)  
8. **Alertar indisponibilidade** → validação server-side (422) + toast no front  
9. **Mostrar estoque atual** → o estoque disponível de cada produto é exibido logo abaixo do campo de quantidade, após a seleção do item, usando o valor `qty_stock` retornado pela API.

> Os itens acima refletem integralmente os requisitos 1–9 do teste. O README cumpre o item 10 (decisões e instruções de execução).
 
## Modelagem de Dados

- **products**: `id`, `name`, `price` (decimal 10,2), `qty_stock` (int), timestamps 
- **orders**: `id`, `customer_name`, `delivery_date` (datetime), `total` (decimal 10,2), timestamps  
- **order_items**: `id`, `order_id` (FK), `product_id` (FK), `qty` (int), `price` (decimal 10,2), `subtotal` (decimal 10,2), timestamps 

## API (Laravel)

### Rotas
- `GET /api/produtos` → lista produtos (inclui `qty_stock`)  
- `GET /api/pedidos` → lista pedidos (com totals)  
- `POST /api/pedidos` → cria pedido com payload:

    ```
  {
    "customer_name": "Fulano",
    "delivery_date": "2025-10-31 13:30:00",
    "items": [
      { "product_id": 1, "qty": 2 },
      { "product_id": 5, "qty": 1 }
    ]
  }

## Front-end (Vue 3)

### Páginas principais
- `OrdersList.vue` - tabela (`PrimeVue DataTable`) com ordenação e paginação.
- `OrderPage.vue` - formulário reativo de novo pedido/visualização.

### Componentes/Composables
- Toast global (`PrimeVue`) no `App.vue`.
- Overlay (`LoadingOverlay.vue`) registrado via plugin para poder chamar `showOverlay()`/`hideOverlay()` sem importar localmente.
- Helpers: formatação de moeda/data.

### AutoComplete (produto)
- `v-model="item.product"` (objeto) + `optionLabel="name"` + `forceSelection`
- No `@item-select`, gravamos `product_id`, `price` e resetamos `qty=1`.

## Validação, Erros (422) e UX

- Front-end impede envio quando faltam campos (nome, data, pelo menos 1 item).
- Back-end retorna 422 detalhando quais itens estouraram o estoque.
- O front captura o erro e mostra toast.
- Por que redundante? UX melhor (feedback imediato) + segurança (regra confiável no servidor).

## Overlay/Loading Global

Para evitar `import { showOverlay }` em todo lugar, foi criado um plugin global `LoadingOverlay`

## Como Rodar - Docker

O projeto pode ser executado de forma totalmente containerizada via **Docker Compose**, com o **Laravel** e o **Vue 3 + Vite** em serviços separados.

### Requisitos
- **Docker Desktop** (com WSL 2 habilitado no Windows)  
- Um servidor **MySQL local** acessível em `host.docker.internal` (ex.: o MySQL do XAMPP)

### Banco de dados
Antes de iniciar os containers, crie o banco `supermarket_orders` no seu MySQL local (via phpMyAdmin ou CLI).  

Em seguida, defina o **usuário e senha do banco** no arquivo `.env.example` do Laravel, por exemplo:


    DB_CONNECTION=mysql
    DB_HOST=host.docker.internal
    DB_PORT=3306
    DB_DATABASE=supermarket_orders
    DB_USERNAME=root
    DB_PASSWORD=password

Na raiz do projeto, execute o comando abaixo:

    docker compose up --build

Isso irá:
- Construir as imagens (Laravel e Vue).
- Executar automaticamente as migrations e importar os produtos.
- Iniciar o servidor Laravel em http://localhost:8000
- Iniciar o front-end Vue em http://localhost:5173

## Testes

Um teste automatizado foi implementado com o PHPUnit, framework nativo do Laravel. O teste valida o endpoint `/api/produtos`, garantindo que a API retorne uma resposta JSON com status 200.

Para executar o teste:

    php artisan test --filter=ProductControllerTest