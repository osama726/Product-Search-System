<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Search API Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background:
                radial-gradient(circle at top left, rgba(59,130,246,0.12), transparent 25%),
                radial-gradient(circle at bottom right, rgba(14,165,233,0.14), transparent 30%),
                #f8fafc;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <header class="mb-8 rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-sky-600">Product Search System</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-900">API Demo Dashboard</h1>
                </div>
                <div class="rounded-full bg-sky-50 px-4 py-2 text-sm font-medium text-sky-700 ring-1 ring-sky-200">
                    Fast response testing with Blade UI
                </div>
            </div>
        </header>

        <div class="grid gap-8 xl:grid-cols-[420px_minmax(0,1fr)]">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-xl font-bold text-slate-900">Search request</h2>

                <form id="searchForm" class="space-y-4">
                    <div>
                        <label for="q" class="mb-1 block text-sm font-medium text-slate-700">Keyword</label>
                        <input id="q" name="q" type="text" value="laptop" placeholder="Search products..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="min_price" class="mb-1 block text-sm font-medium text-slate-700">Min price</label>
                            <input id="min_price" name="min_price" type="number" step="0.01" value="100" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                        </div>
                        <div>
                            <label for="max_price" class="mb-1 block text-sm font-medium text-slate-700">Max price</label>
                            <input id="max_price" name="max_price" type="number" step="0.01" value="1000" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="category_id" class="mb-1 block text-sm font-medium text-slate-700">Category</label>
                            <select id="category_id" name="category_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                                <option value="">All categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="sort" class="mb-1 block text-sm font-medium text-slate-700">Sort</label>
                            <select id="sort" name="sort" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                                <option value="relevance">Relevance</option>
                                <option value="price">Price</option>
                                <option value="created_at">Created date</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="direction" class="mb-1 block text-sm font-medium text-slate-700">Direction</label>
                            <select id="direction" name="direction" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                                <option value="desc">Desc</option>
                                <option value="asc">Asc</option>
                            </select>
                        </div>
                        <div>
                            <label for="per_page" class="mb-1 block text-sm font-medium text-slate-700">Per page</label>
                            <select id="per_page" name="per_page" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                                <option value="6">6</option>
                                <option value="12" selected>12</option>
                                <option value="24">24</option>
                            </select>
                        </div>
                    </div>

                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                        <input id="only_available" name="only_available" type="checkbox" checked class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <span>Only available</span>
                    </label>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-500">
                            Run search
                        </button>
                        <button type="button" id="resetBtn" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Reset
                        </button>
                    </div>
                </form>
            </section>

            <section class="space-y-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h2 class="text-xl font-bold text-slate-900">Request URL</h2>
                        <span id="statusBadge" class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">Idle</span>
                    </div>
                    <div class="rounded-2xl bg-slate-950 p-3 text-sm text-slate-200 break-all">
                        <code id="requestUrl">/api/products/search?q=laptop&min_price=100&max_price=1000&only_available=true&sort=relevance&direction=desc&per_page=12</code>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-slate-900">Results</h2>
                        <span id="resultCount" class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">0 items</span>
                    </div>

                    <div id="resultGrid" class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3"></div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-slate-900">Raw JSON</h2>
                    </div>
                    <pre id="responseJson" class="max-h-[420px] overflow-auto rounded-2xl bg-slate-950 p-4 text-sm text-slate-200">{
  "success": false,
  "message": "No request executed yet.",
  "data": []
}</pre>
                </div>
            </section>
        </div>
    </div>

    <script>
        const form = document.getElementById('searchForm');
        const requestUrlEl = document.getElementById('requestUrl');
        const resultGrid = document.getElementById('resultGrid');
        const responseJson = document.getElementById('responseJson');
        const resultCount = document.getElementById('resultCount');
        const statusBadge = document.getElementById('statusBadge');

        function buildUrl() {
            const params = new URLSearchParams();

            const q = document.getElementById('q').value.trim();
            const minPrice = document.getElementById('min_price').value;
            const maxPrice = document.getElementById('max_price').value;
            const categoryId = document.getElementById('category_id').value;
            const onlyAvailable = document.getElementById('only_available').checked;
            const sort = document.getElementById('sort').value;
            const direction = document.getElementById('direction').value;
            const perPage = document.getElementById('per_page').value;

            if (q) params.set('q', q);
            if (minPrice) params.set('min_price', minPrice);
            if (maxPrice) params.set('max_price', maxPrice);
            if (categoryId) params.set('category_id', categoryId);
            if (onlyAvailable) params.set('only_available', 'true');
            if (sort) params.set('sort', sort);
            if (direction) params.set('direction', direction);
            if (perPage) params.set('per_page', perPage);

            return '/api/products/search?' + params.toString();
        }

        function renderProducts(items) {
            if (!Array.isArray(items) || items.length === 0) {
                resultGrid.innerHTML = `
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                        No results found for this filter set.
                    </div>
                `;
                return;
            }

            resultGrid.innerHTML = items.map(item => `
                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                    <div class="mb-2 flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">${item.name}</h3>
                            <p class="text-xs uppercase tracking-wide text-slate-500">${item.category?.name ?? 'General'}</p>
                        </div>
                        <span class="rounded-full ${item.is_available ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'} px-2 py-1 text-[10px] font-semibold uppercase">
                            ${item.is_available ? 'Available' : 'Sold out'}
                        </span>
                    </div>

                    <p class="mb-3 line-clamp-3 text-sm text-slate-600">${item.description || 'No description provided.'}</p>

                    <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                        <div>
                            <p class="text-xs text-slate-500">Price</p>
                            <p class="text-lg font-bold text-slate-900">$${Number(item.price).toFixed(2)}</p>
                        </div>
                        <div class="text-right text-xs text-slate-500">
                            <p>ID: ${item.id}</p>
                            <p>${new Date(item.created_at).toLocaleDateString()}</p>
                        </div>
                    </div>
                </article>
            `).join('');
        }

        async function fetchProducts() {
            const url = buildUrl();
            requestUrlEl.textContent = url;
            statusBadge.textContent = 'Loading...';
            statusBadge.className = 'rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700';

            try {
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Request failed');
                }

                const items = data?.data ?? [];
                renderProducts(items);
                responseJson.textContent = JSON.stringify(data, null, 2);
                resultCount.textContent = `${items.length} item${items.length === 1 ? '' : 's'}`;
                resultCount.className = 'rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200';
                statusBadge.textContent = 'Success';
                statusBadge.className = 'rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700';
            } catch (error) {
                resultGrid.innerHTML = `
                    <div class="col-span-full rounded-2xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-700">
                        ${error.message}
                    </div>
                `;
                responseJson.textContent = JSON.stringify({ success: false, message: error.message }, null, 2);
                resultCount.textContent = 'Request error';
                resultCount.className = 'rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 ring-1 ring-rose-200';
                statusBadge.textContent = 'Failed';
                statusBadge.className = 'rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-700';
            }
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            fetchProducts();
        });

        document.getElementById('resetBtn').addEventListener('click', function () {
            document.getElementById('q').value = 'laptop';
            document.getElementById('min_price').value = 100;
            document.getElementById('max_price').value = 1000;
            document.getElementById('category_id').value = '';
            document.getElementById('only_available').checked = true;
            document.getElementById('sort').value = 'relevance';
            document.getElementById('direction').value = 'desc';
            document.getElementById('per_page').value = 12;
            fetchProducts();
        });

        fetchProducts();
    </script>
</body>
</html>
