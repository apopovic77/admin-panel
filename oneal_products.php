<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>O'Neal Products Browser</title>
    <style>
        :root {
            --text: #1e293b; --muted: #475569; --brand: #1f2937; --brand-2: #8B9DC3;
            --ring: rgba(148, 163, 184, .3); --surface: rgba(255, 255, 255, 0.98);
            --background-gradient: linear-gradient(to bottom, #ffffff, #f8fafc, #e2e8f0);
            --radius-lg: 16px; --radius-md: 12px; --radius-sm: 8px;
            --shadow-primary: 0 10px 30px rgba(0, 0, 0, .07); --gap: 24px;
            --font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, Roboto, system-ui, sans-serif;
        }
        body {
            font-family: var(--font-family); margin: 0; background: var(--background-gradient);
            color: var(--text); padding-bottom: 64px;
        }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 var(--gap); }
        h1 {
            font-size: clamp(34px, 6.2vw, 76px); font-weight: 700;
            padding-top: 32px; margin-bottom: 32px; text-align: center;
        }
        .nav-links {
            text-align: center; margin-bottom: 32px;
        }
        .nav-links a {
            margin: 0 12px; padding: 8px 16px; text-decoration: none; color: var(--brand);
            border: 1px solid var(--ring); border-radius: var(--radius-sm); background: var(--surface);
        }
        .nav-links a:hover { background: #f1f5f9; }

        .filters {
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-lg); padding: var(--gap);
            margin-bottom: var(--gap); box-shadow: var(--shadow-primary);
        }
        .filter-row {
            display: flex; flex-wrap: wrap; gap: 16px; align-items: center; margin-bottom: 16px;
        }
        .filter-row label { font-weight: 500; color: var(--muted); min-width: 80px; }
        .filter-row input, .filter-row select {
            border: 1px solid var(--ring); border-radius: var(--radius-sm);
            padding: 10px 14px; font-size: 1em; background-color: #f8fafc; flex: 1; min-width: 200px;
        }
        .filter-row button {
            padding: 10px 24px; border-radius: var(--radius-sm); border: 1px solid var(--brand);
            background: var(--brand); color: white; cursor: pointer; font-weight: 600;
        }
        .filter-row button:hover { background: #374151; }

        .stats {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px; margin-bottom: var(--gap);
        }
        .stat-card {
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-md); padding: 20px; text-align: center;
        }
        .stat-value { font-size: 32px; font-weight: 700; color: var(--brand); }
        .stat-label { font-size: 14px; color: var(--muted); margin-top: 8px; }

        .products-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .product-card {
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-md); overflow: hidden;
            transition: all 0.2s; cursor: pointer;
        }
        .product-card:hover { box-shadow: var(--shadow-primary); transform: translateY(-2px); }
        .product-image {
            width: 100%; aspect-ratio: 1; object-fit: cover; background: #f8fafc;
        }
        .product-info { padding: 16px; }
        .product-name { font-weight: 600; color: var(--brand); margin-bottom: 8px; }
        .product-price { font-size: 20px; font-weight: 700; color: #059669; margin-bottom: 8px; }
        .product-meta {
            display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px;
        }
        .pill {
            font-size: 11px; font-weight: 500; padding: 3px 8px;
            border-radius: 12px; background-color: #e2e8f0; color: #475569;
        }
        .pill.category { background-color: #dbeafe; color: #1e40af; }
        .pill.season { background-color: #fef3c7; color: #92400e; }

        .loading {
            text-align: center; padding: 40px; font-size: 18px; color: var(--muted);
        }
        .error {
            background: #fee2e2; border: 1px solid #fecaca; border-radius: var(--radius-md);
            padding: 20px; margin: 20px 0; color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>O'Neal Products Browser</h1>

        <div class="nav-links">
            <a href="storage2.php">Storage</a>
            <a href="oneal_products.php" style="background: #e0e7ff; border-color: #4338ca;">Products</a>
            <a href="vector_search.php">Vector Search</a>
        </div>

        <div class="stats" id="stats">
            <div class="stat-card">
                <div class="stat-value" id="total-count">-</div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="mtb-count">-</div>
                <div class="stat-label">MTB Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="mx-count">-</div>
                <div class="stat-label">MX Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="filtered-count">-</div>
                <div class="stat-label">Showing</div>
            </div>
        </div>

        <div class="filters">
            <h2>Filters</h2>
            <div class="filter-row">
                <label>Search:</label>
                <input type="text" id="search" placeholder="Search by name, category...">
            </div>
            <div class="filter-row">
                <label>Category:</label>
                <select id="category">
                    <option value="">All Categories</option>
                </select>

                <label>Source:</label>
                <select id="source">
                    <option value="">All</option>
                    <option value="mtb">MTB</option>
                    <option value="mx">MX</option>
                </select>

                <label>Season:</label>
                <select id="season">
                    <option value="">All Seasons</option>
                </select>
            </div>
            <div class="filter-row">
                <label>Price Range:</label>
                <input type="number" id="price-min" placeholder="Min €" style="flex: 0 1 120px;">
                <span>-</span>
                <input type="number" id="price-max" placeholder="Max €" style="flex: 0 1 120px;">

                <button onclick="applyFilters()">Apply Filters</button>
                <button onclick="resetFilters()" style="background: #6b7280;">Reset</button>
            </div>
        </div>

        <div id="error-container"></div>
        <div id="loading" class="loading">Loading products...</div>
        <div class="products-grid" id="products-grid"></div>
    </div>

    <script>
        const API_BASE = 'https://oneal-api.arkturian.com/v1';
        const API_KEY = 'oneal_demo_token';

        let allProducts = [];
        let filteredProducts = [];

        async function loadProducts() {
            try {
                const response = await fetch(`${API_BASE}/products?limit=1000`, {
                    headers: { 'X-API-Key': API_KEY }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                allProducts = data.results || [];
                filteredProducts = allProducts;

                updateStats();
                populateFilters();
                renderProducts();

                document.getElementById('loading').style.display = 'none';
            } catch (error) {
                console.error('Error loading products:', error);
                document.getElementById('loading').style.display = 'none';
                document.getElementById('error-container').innerHTML = `
                    <div class="error">
                        <strong>Error loading products:</strong> ${error.message}
                    </div>
                `;
            }
        }

        function updateStats() {
            const mtbCount = allProducts.filter(p => p.meta?.source === 'mtb').length;
            const mxCount = allProducts.filter(p => p.meta?.source === 'mx').length;

            document.getElementById('total-count').textContent = allProducts.length;
            document.getElementById('mtb-count').textContent = mtbCount;
            document.getElementById('mx-count').textContent = mxCount;
            document.getElementById('filtered-count').textContent = filteredProducts.length;
        }

        function populateFilters() {
            // Categories
            const categories = new Set();
            allProducts.forEach(p => {
                if (p.category) {
                    p.category.forEach(cat => categories.add(cat));
                }
            });

            const categorySelect = document.getElementById('category');
            Array.from(categories).sort().forEach(cat => {
                const option = document.createElement('option');
                option.value = cat;
                option.textContent = cat;
                categorySelect.appendChild(option);
            });

            // Seasons
            const seasons = new Set();
            allProducts.forEach(p => {
                if (p.season) seasons.add(p.season);
            });

            const seasonSelect = document.getElementById('season');
            Array.from(seasons).sort((a, b) => b - a).forEach(season => {
                const option = document.createElement('option');
                option.value = season;
                option.textContent = season;
                seasonSelect.appendChild(option);
            });
        }

        function applyFilters() {
            const search = document.getElementById('search').value.toLowerCase();
            const category = document.getElementById('category').value;
            const source = document.getElementById('source').value;
            const season = document.getElementById('season').value;
            const priceMin = parseFloat(document.getElementById('price-min').value) || 0;
            const priceMax = parseFloat(document.getElementById('price-max').value) || Infinity;

            filteredProducts = allProducts.filter(product => {
                // Search
                if (search && !product.name.toLowerCase().includes(search)) {
                    const categoryMatch = product.category?.some(c => c.toLowerCase().includes(search));
                    if (!categoryMatch) return false;
                }

                // Category
                if (category && !product.category?.includes(category)) return false;

                // Source
                if (source && product.meta?.source !== source) return false;

                // Season
                if (season && product.season != season) return false;

                // Price
                if (product.price?.value) {
                    if (product.price.value < priceMin || product.price.value > priceMax) return false;
                }

                return true;
            });

            updateStats();
            renderProducts();
        }

        function resetFilters() {
            document.getElementById('search').value = '';
            document.getElementById('category').value = '';
            document.getElementById('source').value = '';
            document.getElementById('season').value = '';
            document.getElementById('price-min').value = '';
            document.getElementById('price-max').value = '';

            filteredProducts = allProducts;
            updateStats();
            renderProducts();
        }

        function renderProducts() {
            const grid = document.getElementById('products-grid');

            if (filteredProducts.length === 0) {
                grid.innerHTML = '<div class="loading">No products found matching your filters.</div>';
                return;
            }

            grid.innerHTML = filteredProducts.map(product => {
                const imageUrl = product.media?.[0]?.src || 'https://via.placeholder.com/300?text=No+Image';
                const price = product.price ? product.price.formatted : 'N/A';

                const categories = product.category?.map(cat =>
                    `<span class="pill category">${cat}</span>`
                ).join('') || '';

                const seasonPill = product.season ?
                    `<span class="pill season">${product.season}</span>` : '';

                return `
                    <div class="product-card" onclick="openProduct('${product.id}')">
                        <img src="${imageUrl}" class="product-image" alt="${product.name}"
                             onerror="this.src='https://via.placeholder.com/300?text=Image+Error'">
                        <div class="product-info">
                            <div class="product-name">${product.name}</div>
                            <div class="product-price">${price}</div>
                            <div class="product-meta">
                                ${categories}
                                ${seasonPill}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function openProduct(productId) {
            const product = allProducts.find(p => p.id === productId);
            if (product?.meta?.product_url) {
                window.open(product.meta.product_url, '_blank');
            }
        }

        // Search on Enter key
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('search').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') applyFilters();
            });

            loadProducts();
        });
    </script>
</body>
</html>
