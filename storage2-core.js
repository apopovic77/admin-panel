// storage2-core.js - Configuration, DOM Elements, Modals, Utilities

// --- Configuration (set by PHP in HTML before this script) ---
// const API_BASE_URL = '...';
// const SHARE_BASE_URL = '...';

const DEFAULT_TENANT_FALLBACK = (window.location.hostname || '').includes('arkserver')
    ? 'arkserver'
    : 'arkturian';

let TENANTS = {};
let currentTenant = localStorage.getItem('selectedTenant') || DEFAULT_TENANT_FALLBACK;
let API_KEY = 'Inetpass1';

// --- DOM Elements ---
const tenantSelect = document.getElementById('tenant-select');
const dropZone = document.getElementById('drop-zone');
const fileListContainer = document.getElementById('file-list');
const uploadProgress = document.getElementById('upload-progress');
const uploadStatusList = document.getElementById('upload-status-list');
const uploadProgressText = document.getElementById('upload-progress-text');
const ownerEmailInput = document.getElementById('owner-email-input');
const searchNameInput = document.getElementById('search-name-input');
const searchCollectionInput = document.getElementById('search-collection-input');
const searchIdInput = document.getElementById('search-id-input');
const bulkActionsContainer = document.getElementById('bulk-actions-container');
const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

// --- Modals ---
const deleteModal = document.getElementById('delete-modal');
const modalConfirmBtn = document.getElementById('modal-confirm');
const modalCancelBtn = document.getElementById('modal-cancel');
const errorModal = document.getElementById('error-modal');
const errorLogContent = document.getElementById('error-log-content');
const errorModalCloseBtn = document.getElementById('error-modal-close');
const editorModal = document.getElementById('editor-modal');
const editorText = document.getElementById('editor-text');
const editorTitle = document.getElementById('editor-title');
const editorCancel = document.getElementById('editor-cancel');
const editorSave = document.getElementById('editor-save');
const newMdBtn = document.getElementById('new-md-btn');

let editTargetId = null;
let fileToDeleteId = null;

// --- Tenant Management ---
async function loadTenants() {
    try {
        const res = await fetch(`${API_BASE_URL}/tenants/keys`, {
            headers: { 'X-API-KEY': 'Inetpass1' }
        });
        if (!res.ok) throw new Error(await res.text());
        const data = await res.json();
        TENANTS = {};
        Object.entries(data).forEach(([key, tenantId]) => {
            if (!tenantId) return;
            if (!TENANTS[tenantId]) {
                TENANTS[tenantId] = [];
            }
            TENANTS[tenantId].push(key);
        });
    } catch (error) {
        console.error('Failed to load tenant keys:', error);
        TENANTS = {
            [DEFAULT_TENANT_FALLBACK]: ['Inetpass1']
        };
    }

    const tenantIds = Object.keys(TENANTS);
    if (!tenantIds.includes(currentTenant)) {
        currentTenant = tenantIds[0] || DEFAULT_TENANT_FALLBACK;
        localStorage.setItem('selectedTenant', currentTenant);
    }
    API_KEY = (TENANTS[currentTenant] && TENANTS[currentTenant][0]) || 'Inetpass1';

    if (tenantSelect) {
        tenantSelect.innerHTML = tenantIds
            .map(id => `<option value="${id}">${id}</option>`)
            .join('');
        tenantSelect.value = currentTenant;
    }
}

// --- Modal Functions ---
function showDeleteModal(fileId) {
    fileToDeleteId = fileId;
    deleteModal.style.display = 'flex';
}

function hideDeleteModal() {
    fileToDeleteId = null;
    deleteModal.style.display = 'none';
}

function showErrorModal(base64Error) {
    try {
        errorLogContent.textContent = atob(base64Error);
    } catch (e) {
        errorLogContent.textContent = "Error decoding log.";
    }
    errorModal.style.display = 'flex';
}

function hideErrorModal() {
    errorModal.style.display = 'none';
}

function showEditor(id, name, url) {
    editTargetId = id;
    editorTitle.textContent = `Edit ${name}`;
    editorText.value = '';
    editorModal.style.display = 'flex';
    const fetchUrl = url.includes('?') ? `${url}&_=${Date.now()}` : `${url}?_=${Date.now()}`;
    fetch(fetchUrl)
        .then(r => r.text())
        .then(t => { editorText.value = t; })
        .catch(() => { editorText.value = ''; });
}

function hideEditor() {
    editTargetId = null;
    editorModal.style.display = 'none';
}

// --- Utility Functions ---
function debounce(fn, delay = 300) {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(null, args), delay);
    };
}
