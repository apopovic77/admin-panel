<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collections Manager - arkturian.com</title>
    <style>
        :root {
            --text: #1e293b; --muted: #475569; --brand: #1f2937; --brand-2: #8B9DC3;
            --ring: rgba(148, 163, 184, .3); --surface: rgba(255, 255, 255, 0.98);
            --background-gradient: linear-gradient(to bottom, #ffffff, #f8fafc, #e2e8f0);
            --radius-lg: 16px; --radius-md: 12px; --radius-sm: 8px;
            --shadow-primary: 0 10px 30px rgba(0, 0, 0, .07); --gap: 24px;
            --font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, Roboto, system-ui, sans-serif;
            --h2-size: clamp(22px, 3.6vw, 34px); --kicker-size: 12px;
            --success-color: #d4edda; --error-color: #f8d7da;
        }
        body { 
            font-family: var(--font-family); margin: 0; background: var(--background-gradient);
            color: var(--text); padding: 20px; min-height: 100vh;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 var(--gap); }
        .header { 
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-lg); padding: var(--gap);
            margin-bottom: 32px; box-shadow: var(--shadow-primary);
        }
        .header h1 { 
            margin: 0; color: var(--text); font-weight: 700;
            font-size: var(--h2-size); text-align: left;
        }
        h2 {
            font-size: var(--h2-size); font-weight: 600; margin-bottom: var(--gap);
            border-bottom: 1px solid var(--ring); padding-bottom: 16px;
        }
        .card { 
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-lg); padding: var(--gap);
            box-shadow: var(--shadow-primary); margin-bottom: var(--gap);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block; margin-bottom: 8px; font-weight: 600;
            color: var(--muted);
        }
        select {
            width: 100%; padding: 12px; border: 1px solid var(--ring);
            border-radius: var(--radius-sm); font-family: inherit;
            background: var(--surface); color: var(--text);
        }
        select:focus {
            outline: none; border-color: var(--brand-2);
            box-shadow: 0 0 0 3px rgba(139, 157, 195, 0.1);
        }
        .collections-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--gap); margin-top: var(--gap);
        }
        .collection-card {
            background: rgba(248, 250, 252, 0.8); border: 1px solid var(--ring);
            border-radius: var(--radius-md); padding: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        .collection-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: var(--brand-2);
        }
        .collection-name {
            font-size: 18px; font-weight: 600; margin-bottom: 8px;
            color: var(--text);
        }
        .collection-stats {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 12px;
        }
        .item-count {
            background: var(--brand-2); color: white; padding: 4px 12px;
            border-radius: 999px; font-size: 14px; font-weight: 500;
        }
        .collection-id {
            font-size: 12px; color: var(--muted); font-family: 'Courier New', monospace;
        }
        .loading {
            text-align: center; padding: 40px; color: var(--muted);
            font-style: italic;
        }
        .error {
            background: var(--error-color); border: 1px solid #dc3545;
            color: #721c24; padding: 12px; border-radius: var(--radius-sm);
            margin: 16px 0;
        }
        .empty-state {
            text-align: center; padding: 60px 20px;
            color: var(--muted);
        }
        .empty-state h3 {
            margin-bottom: 8px; color: var(--text);
        }
        .public-badge {
            background: #28a745; color: white; padding: 2px 8px;
            border-radius: 4px; font-size: 12px; font-weight: 500;
            text-transform: uppercase;
        }
        .user-info {
            background: rgba(139, 157, 195, 0.1); padding: 12px;
            border-radius: var(--radius-sm); margin-bottom: 20px;
            font-size: 14px; color: var(--muted);
        }
        .back-button {
            background: var(--brand-2); color: white; border: none;
            padding: 8px 16px; border-radius: var(--radius-sm);
            cursor: pointer; font-weight: 500; margin-bottom: 20px;
            transition: background 0.2s ease;
        }
        .back-button:hover {
            background: var(--brand);
        }
        .items-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 16px; margin-top: var(--gap);
        }
        .item-card {
            background: rgba(248, 250, 252, 0.9); border: 1px solid var(--ring);
            border-radius: var(--radius-sm); padding: 16px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex; gap: 12px; align-items: flex-start;
        }
        .item-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .item-thumbnail {
            width: 60px; height: 60px; border-radius: var(--radius-sm);
            object-fit: cover; flex-shrink: 0;
            background: rgba(248, 250, 252, 0.8); border: 1px solid var(--ring);
        }
        .item-content {
            flex: 1; min-width: 0;
        }
        .item-name {
            font-size: 16px; font-weight: 600; margin-bottom: 8px;
            color: var(--text); word-break: break-word;
        }
        .item-meta {
            font-size: 12px; color: var(--muted);
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 8px;
        }
        .item-size {
            background: var(--ring); color: var(--text);
            padding: 2px 6px; border-radius: 4px; font-weight: 500;
        }
        .collection-detail h2 {
            display: flex; align-items: center; gap: 12px;
        }
        .collection-badge {
            background: var(--brand-2); color: white; padding: 4px 12px;
            border-radius: 999px; font-size: 12px; font-weight: 500;
        }
        .item-actions {
            margin-top: 8px; display: flex; gap: 12px; flex-wrap: wrap;
        }
        .action-link {
            font-size: 8pt; color: var(--brand); text-decoration: none;
            cursor: pointer; transition: color 0.2s ease;
        }
        .action-link:hover {
            color: var(--brand-2); text-decoration: underline;
        }
        .delete-btn {
            background: #dc3545; color: white; border: none;
            padding: 4px 8px; border-radius: 4px; cursor: pointer;
            font-size: 8pt; transition: background 0.2s ease;
        }
        .delete-btn:hover {
            background: #c82333;
        }
        .delete-collection-btn {
            background: #dc3545; color: white; border: none;
            padding: 8px 16px; border-radius: var(--radius-sm);
            cursor: pointer; font-weight: 500; margin-left: 12px;
            transition: background 0.2s ease;
        }
        .delete-collection-btn:hover {
            background: #c82333;
        }
        .delete-all-btn {
            background: #dc3545; color: white; border: none;
            padding: 8px 16px; border-radius: var(--radius-sm);
            cursor: pointer; font-weight: 500; margin-left: 12px;
            transition: background 0.2s ease;
        }
        .delete-all-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container">
        <div class="header">
            <h1>📚 Collections Manager</h1>
        </div>
        
        <div class="card">
            <div class="form-group">
                <label for="email-select">Select User Email:</label>
                <select id="email-select" onchange="loadCollections()">
                    <option value="">Choose an email...</option>
                    <option value="public">🌍 Public Collections (No Owner)</option>
                </select>
            </div>
            
            <div id="user-info" class="user-info" style="display: none;">
                <strong>Selected:</strong> <span id="selected-email"></span>
            </div>
        </div>
        
        <div id="collections-container">
            <div class="loading">Select an email to view collections...</div>
        </div>
    </div>

    <script>
        const API_BASE = 'https://api-storage.arkturian.com';
        let emailsWithCollections = [];
        let currentView = 'collections'; // 'collections' or 'items'
        let selectedCollection = null;
        
        async function loadEmailsWithCollections() {
            try {
                // Get all users who have collections
                const response = await fetch(`${API_BASE}/storage/admin/users-with-collections`, {
                    headers: {
                        'X-API-KEY': 'Inetpass1'
                    }
                });
                if (!response.ok) {
                    throw new Error('Failed to fetch users with collections');
                }
                
                emailsWithCollections = await response.json();
                
                const select = document.getElementById('email-select');
                // Clear existing options except first two (placeholder and public)
                while (select.children.length > 2) {
                    select.removeChild(select.lastChild);
                }
                
                // Add email options
                emailsWithCollections.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.email;
                    option.textContent = `${user.email} (${user.collection_count} collections)`;
                    select.appendChild(option);
                });
                
            } catch (error) {
                console.error('Error loading emails:', error);
                document.getElementById('collections-container').innerHTML = 
                    `<div class="error">Error loading users: ${error.message}</div>`;
            }
        }
        
        async function loadCollections() {
            const select = document.getElementById('email-select');
            const selectedEmail = select.value;
            const container = document.getElementById('collections-container');
            const userInfo = document.getElementById('user-info');
            const selectedEmailSpan = document.getElementById('selected-email');
            
            if (!selectedEmail) {
                container.innerHTML = '<div class="loading">Select an email to view collections...</div>';
                userInfo.style.display = 'none';
                return;
            }
            
            // Update user info
            if (selectedEmail === 'public') {
                selectedEmailSpan.textContent = '🌍 Public Collections (No Owner)';
            } else {
                selectedEmailSpan.textContent = selectedEmail;
            }
            userInfo.style.display = 'block';
            
            // Show loading state
            container.innerHTML = '<div class="loading">Loading collections...</div>';
            
            try {
                let url;
                if (selectedEmail === 'public') {
                    url = `${API_BASE}/storage/admin/collections?public_only=true`;
                } else {
                    url = `${API_BASE}/storage/admin/collections?user_email=${encodeURIComponent(selectedEmail)}`;
                }
                
                const response = await fetch(url, {
                    headers: {
                        'X-API-KEY': 'Inetpass1'
                    }
                });
                if (!response.ok) {
                    throw new Error('Failed to fetch collections');
                }
                
                const collections = await response.json();
                
                if (collections.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <h3>No Collections Found</h3>
                            <p>This ${selectedEmail === 'public' ? 'public area has' : 'user has'} no collections yet.</p>
                        </div>
                    `;
                    return;
                }
                
                // Render collections
                const collectionsHTML = collections.map(collection => `
                    <div class="collection-card" onclick="loadCollectionItems('${collection.id}', '${collection.name || collection.id}', '${selectedEmail}')">
                        <div class="collection-name">
                            ${collection.name || collection.id}
                            ${selectedEmail === 'public' ? '<span class="public-badge">Public</span>' : ''}
                        </div>
                        <div class="collection-id">ID: ${collection.id}</div>
                        <div class="collection-stats">
                            <div>
                                <strong>Items:</strong> ${collection.item_count}
                            </div>
                            <div class="item-count">${collection.item_count}</div>
                        </div>
                    </div>
                `).join('');
                
                container.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--gap);">
                        <h2>Collections for ${selectedEmail === 'public' ? 'Public Area' : selectedEmail}</h2>
                        ${selectedEmail !== 'public' ? 
                            `<button class="delete-all-btn" onclick="deleteAllFromUser('${selectedEmail}')" title="Delete ALL files and collections from this user">🗑️ Delete All User Files</button>` : 
                            ''
                        }
                    </div>
                    <div class="collections-grid">
                        ${collectionsHTML}
                    </div>
                `;
                
            } catch (error) {
                console.error('Error loading collections:', error);
                container.innerHTML = `<div class="error">Error loading collections: ${error.message}</div>`;
            }
        }
        
        async function loadCollectionItems(collectionId, collectionName, userEmail) {
            const container = document.getElementById('collections-container');
            selectedCollection = { id: collectionId, name: collectionName, userEmail: userEmail };
            currentView = 'items';
            
            // Show loading state
            container.innerHTML = '<div class="loading">Loading collection items...</div>';
            
            try {
                // Use the correct storage/list endpoint with mine=false for admin access
                let url;
                if (collectionId === 'null') {
                    // For uncategorized files, filter by user and no collection_id
                    url = `${API_BASE}/storage/list?mine=false`;
                    // We'll filter on the frontend since we can't easily query for null collection_id
                } else {
                    url = `${API_BASE}/storage/list?collection_id=${encodeURIComponent(collectionId)}&mine=false`;
                }
                
                const response = await fetch(url, {
                    headers: {
                        'X-API-KEY': 'Inetpass1'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to fetch collection items');
                }
                
                const data = await response.json();
                let items = data.items || [];
                
                // If we're looking for uncategorized files, filter by user and null collection_id
                if (collectionId === 'null') {
                    items = items.filter(item => 
                        item.owner_email === userEmail && 
                        (!item.collection_id || item.collection_id === null)
                    );
                }
                
                if (items.length === 0) {
                    container.innerHTML = `
                        <div class="collection-detail">
                            <button class="back-button" onclick="showCollectionsView()">← Back to Collections</button>
                            <h2>
                                ${collectionName || collectionId}
                                <span class="collection-badge">${items.length} items</span>
                            </h2>
                            <div class="empty-state">
                                <h3>No Items Found</h3>
                                <p>This collection is empty.</p>
                            </div>
                        </div>
                    `;
                    return;
                }
                
                // Render items
                const itemsHTML = items.map(item => {
                    const sizeFormatted = formatFileSize(item.file_size_bytes || 0);
                    const createdDate = new Date(item.created_at).toLocaleDateString();
                    
                    // Generate action links
                    let actionsHTML = `
                        <div class="item-actions">
                            <a href="${item.file_url}" class="action-link" download target="_blank">download</a>
                            <a href="#" class="action-link" onclick="shareItem('${item.file_url}', '${item.title || item.original_filename}'); return false;">share</a>`;
                    
                    // Add HLS/VOD link if available
                    if (item.hls_url && item.hls_url.trim()) {
                        const vodUrl = collectionId && collectionId !== 'null' 
                            ? `https://admin.arkturian.com/vod.php?current_id=${item.id}&collection_id=${encodeURIComponent(collectionId)}`
                            : `https://admin.arkturian.com/vod.php?current_id=${item.id}`;
                        actionsHTML += `<a href="${vodUrl}" class="action-link" target="_blank">hls</a>`;
                    }
                    
                    // Add delete button
                    actionsHTML += `<button class="delete-btn" onclick="deleteItem(${item.id}, '${(item.title || item.original_filename || 'Untitled').replace(/'/g, '\\\'')}')" title="Delete item">×</button>`;
                    
                    actionsHTML += '</div>';
                    
                    // Generate thumbnail HTML
                    const thumbnailHTML = item.thumbnail_url 
                        ? `<img src="${item.thumbnail_url}" alt="Thumbnail" class="item-thumbnail" loading="lazy">`
                        : `<div class="item-thumbnail" style="display: flex; align-items: center; justify-content: center; color: var(--muted); font-size: 24px;">📄</div>`;
                    
                    return `
                        <div class="item-card">
                            ${thumbnailHTML}
                            <div class="item-content">
                                <div class="item-name">${item.title || item.original_filename || 'Untitled'}</div>
                                <div class="item-meta">
                                    <span>Created: ${createdDate}</span>
                                    <span class="item-size">${sizeFormatted}</span>
                                </div>
                                ${actionsHTML}
                            </div>
                        </div>
                    `;
                }).join('');
                
                container.innerHTML = `
                    <div class="collection-detail">
                        <button class="back-button" onclick="showCollectionsView()">← Back to Collections</button>
                        <h2>
                            ${collectionName || collectionId}
                            <span class="collection-badge">${items.length} items</span>
                            ${collectionId !== 'null' ? `<button class="back-button" style="background:#6c757d" onclick="promptRenameCollection('${collectionId}', '${collectionName || collectionId}', '${userEmail}')">Rename</button>` : ''}
                            ${collectionId !== 'null' ? 
                                `<button class="delete-collection-btn" onclick="deleteCollection('${collectionId}', '${collectionName || collectionId}')" title="Delete entire collection">Delete Collection</button>` : 
                                `<button class="delete-all-btn" onclick="deleteAllFromUser('${userEmail}')" title="Delete all uncategorized files from this user">Delete All</button>`
                            }
                        </h2>
                        <div class="items-grid">
                            ${itemsHTML}
                        </div>
                    </div>
                `;
                
            } catch (error) {
                console.error('Error loading collection items:', error);
                container.innerHTML = `
                    <div class="collection-detail">
                        <button class="back-button" onclick="showCollectionsView()">← Back to Collections</button>
                        <div class="error">Error loading collection items: ${error.message}</div>
                    </div>
                `;
            }
        }
        
        function showCollectionsView() {
            currentView = 'collections';
            selectedCollection = null;
            loadCollections(); // Reload the collections view
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function shareItem(fileUrl, itemName) {
            // Try to use Web Share API if available
            if (navigator.share) {
                navigator.share({
                    title: itemName || 'Shared File',
                    url: fileUrl
                }).catch(err => {
                    console.log('Error sharing:', err);
                    fallbackShare(fileUrl, itemName);
                });
            } else {
                fallbackShare(fileUrl, itemName);
            }
        }
        
        function fallbackShare(fileUrl, itemName) {
            // Fallback: copy URL to clipboard
            navigator.clipboard.writeText(fileUrl).then(() => {
                alert(`Link copied to clipboard!\n${fileUrl}`);
            }).catch(err => {
                console.error('Failed to copy link:', err);
                // Ultimate fallback: show the URL
                prompt('Copy this link:', fileUrl);
            });
        }
        
        async function deleteItem(itemId, itemName) {
            if (!confirm(`Are you sure you want to delete "${itemName}"?\n\nThis action cannot be undone.`)) {
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/storage/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-API-KEY': 'Inetpass1'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to delete item');
                }
                
                alert('Item deleted successfully!');
                // Reload the current collection view
                if (selectedCollection) {
                    loadCollectionItems(selectedCollection.id, selectedCollection.name, selectedCollection.userEmail);
                }
            } catch (error) {
                console.error('Error deleting item:', error);
                alert('Error deleting item: ' + error.message);
            }
        }
        
        async function deleteCollection(collectionId, collectionName) {
            if (!confirm(`Are you sure you want to delete the entire collection "${collectionName}"?\n\nThis will delete ALL items in this collection.\nThis action cannot be undone.`)) {
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/storage/admin/cleanup/by-collection`, {
                    method: 'POST',
                    headers: {
                        'X-API-KEY': 'Inetpass1',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        collection_id: collectionId
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Failed to delete collection');
                }
                
                const result = await response.json();
                alert(`Collection deleted successfully!\nDeleted ${result.deleted_count} items.`);
                
                // Go back to collections view
                showCollectionsView();
            } catch (error) {
                console.error('Error deleting collection:', error);
                alert('Error deleting collection: ' + error.message);
            }
        }

        async function promptRenameCollection(oldId, currentName, userEmail){
            const newId = prompt(`Neuen Namen für die Kollektion eingeben:\nAktuell: ${currentName}\nHinweis: dies aktualisiert die collection_id auf allen Objekten.`, currentName || oldId);
            if(!newId){ return; }
            if(newId === oldId){ alert('Der neue Name ist identisch.'); return; }
            try{
                const res = await fetch(`${API_BASE}/storage/admin/collections/rename`,{
                    method:'POST',
                    headers:{ 'X-API-KEY':'Inetpass1','Content-Type':'application/json' },
                    body: JSON.stringify({ old_id: oldId, new_id: newId, owner_email: userEmail === 'public' ? null : userEmail })
                });
                if(!res.ok){ throw new Error(await res.text() || 'Rename failed'); }
                const data = await res.json();
                alert(`Kollektion umbenannt. Aktualisierte Objekte: ${data.updated_count}`);
                loadCollectionItems(newId, newId, userEmail);
            }catch(err){
                console.error('Rename failed', err);
                alert('Fehler beim Umbenennen: ' + err.message);
            }
        }
        
        async function deleteAllFromUser(userEmail) {
            if (!confirm(`Are you sure you want to delete ALL files from user "${userEmail}"?\n\nThis will delete ALL of their files, including organized collections.\nThis action cannot be undone.`)) {
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/storage/admin/cleanup/by-user`, {
                    method: 'POST',
                    headers: {
                        'X-API-KEY': 'Inetpass1',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: userEmail
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Failed to delete user files');
                }
                
                const result = await response.json();
                alert(`All files deleted successfully!\nDeleted ${result.deleted_count} items from ${userEmail}.`);
                
                // Go back to collections view and reload the email list
                showCollectionsView();
                loadEmailsWithCollections(); // Refresh the dropdown
            } catch (error) {
                console.error('Error deleting user files:', error);
                alert('Error deleting user files: ' + error.message);
            }
        }
        
        // Load emails when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadEmailsWithCollections();
        });
    </script>
</body>
</html>