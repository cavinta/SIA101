// ---------- GLOBALS ----------
let editingCategoryId = null;
let editingItemId = null;
let editingSizeId = null;

// CATEGORY ELEMENTS
const categoryTableBody = document.querySelector('#categoryTable tbody');
const categoryNameInput = document.getElementById('categoryName');
const categoryFormTitle = document.getElementById('categoryFormTitle');
const saveCategoryBtn = document.getElementById('saveCategoryBtn');
const cancelCategoryBtn = document.getElementById('cancelCategoryBtn');

// ITEM ELEMENTS
const itemTableBody = document.querySelector('#itemTable tbody');
const itemNameInput = document.getElementById('itemName');
const itemPriceInput = document.getElementById('itemPrice');
const itemCategorySelect = document.getElementById('itemCategory');
const itemFormTitle = document.getElementById('itemFormTitle');
const saveItemBtn = document.getElementById('saveItemBtn');
const cancelItemBtn = document.getElementById('cancelItemBtn');

// ---------- LOAD DATA ----------
function loadCategories(){
  fetch('../api/category/read_all_category.php')
    .then(r => r.json())
    .then(data => {
      if(data.status==='success'){
        categoryTableBody.innerHTML='';
        itemCategorySelect.innerHTML='<option value="">-- Select Category --</option>';
        data.data.forEach(cat=>{
          // Table
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${cat.id}</td>
            <td>${cat.name}</td>
            <td>
              <button onclick="viewCategory(${cat.id})">View</button>
              <button onclick="editCategory(${cat.id})">Edit</button>
              <button onclick="deleteCategory(${cat.id})">Delete</button>
            </td>
          `;
          categoryTableBody.appendChild(tr);

          // Dropdown
          const option = document.createElement('option');
          option.value = cat.id;
          option.textContent = cat.name;
          itemCategorySelect.appendChild(option);
        });
      }
    });
}

function loadItems(){
  fetch('../api/items/read_all_items.php')
    .then(r=>r.json())
    .then(data=>{
      if(data.status==='success'){
        itemTableBody.innerHTML='';
        data.data.forEach(item=>{
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${item.id}</td>
            <td>${item.category_name}</td>
            <td>${item.name}</td>
            <td>₱${parseFloat(item.price).toFixed(2)}</td>
            <td>
              <button onclick="viewItem(${item.id})">View</button>
              <button onclick="editItem(${item.id})">Edit</button>
              <button onclick="deleteItem(${item.id})">Delete</button>
            </td>
          `;
          itemTableBody.appendChild(tr);
        });
      }
    });
}

// ---------- CATEGORY ACTIONS ----------
function viewCategory(id){
  fetch(`../api/category/read_one_category.php?id=${id}`)
    .then(r=>r.json())
    .then(data=>{
      if(data.status==='success'){
        alert(`Category ID: ${data.data.id}\nName: ${data.data.name}`);
      } else alert(data.message);
    });
}

function editCategory(id){
  fetch(`../api/category/read_one_category.php?id=${id}`)
    .then(r=>r.json())
    .then(data=>{
      if(data.status==='success'){
        categoryNameInput.value = data.data.name;
        editingCategoryId = data.data.id;
        categoryFormTitle.textContent='Edit Category';
        saveCategoryBtn.textContent='Update Category';
        cancelCategoryBtn.style.display='inline-block';
      }
    });
}

saveCategoryBtn.addEventListener('click', ()=>{
  const name = categoryNameInput.value.trim();
  if(!name){ alert('Enter category name'); return; }
  const url = editingCategoryId ? '../api/category/edit_category.php' : '../api/category/create_category.php';
  const payload = editingCategoryId ? {id: editingCategoryId,name} : {name};

  fetch(url,{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  })
  .then(r=>r.json())
  .then(data=>{
    if(data.status==='success'){ resetCategoryForm(); loadCategories(); } else alert(data.message);
  });
});

cancelCategoryBtn.addEventListener('click', resetCategoryForm);

function resetCategoryForm(){
  categoryNameInput.value='';
  editingCategoryId=null;
  categoryFormTitle.textContent='Add Category';
  saveCategoryBtn.textContent='Add Category';
  cancelCategoryBtn.style.display='none';
}

function deleteCategory(id){
  if(!confirm('Delete this category?')) return;
  fetch('../api/category/delete_category.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({id})
  }).then(r=>r.json())
    .then(data=>{ if(data.status==='success'){ loadCategories(); loadItems(); } else alert(data.message); });
}

// ---------- ITEM ACTIONS ----------
function viewItem(id){
  fetch(`../api/items/read_one_items.php?id=${id}`)
    .then(r=>r.json())
    .then(data=>{
      if(data.status==='success'){
        alert(`Item ID: ${data.data.id}\nName: ${data.data.name}\nPrice: ₱${data.data.price}\nCategory: ${data.data.category_name}`);
      } else alert(data.message);
    });
}

function editItem(id){
  fetch(`../api/items/read_one_items.php?id=${id}`)
    .then(r=>r.json())
    .then(data=>{
      if(data.status==='success'){
        itemNameInput.value = data.data.name;
        itemPriceInput.value = parseFloat(data.data.price);
        itemCategorySelect.value = data.data.category_id;
        editingItemId = data.data.id;
        itemFormTitle.textContent='Edit Item';
        saveItemBtn.textContent='Update Item';
        cancelItemBtn.style.display='inline-block';
      }
    });
}

saveItemBtn.addEventListener('click', ()=>{
  const name = itemNameInput.value.trim();
  const price = parseFloat(itemPriceInput.value);
  const category_id = parseInt(itemCategorySelect.value);

  if(!name || !category_id || isNaN(price)){ alert('Fill all fields'); return; }

  const url = editingItemId ? '../api/items/edit_items.php' : '../api/items/create_item.php';
  const payload = editingItemId ? {id:editingItemId,name,price,category_id} : {name,price,category_id};

  fetch(url,{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify(payload)
  }).then(r=>r.json())
    .then(data=>{
      if(data.status==='success'){ resetItemForm(); loadItems(); } else alert(data.message);
    });
});

cancelItemBtn.addEventListener('click', resetItemForm);

function resetItemForm(){
  itemNameInput.value='';
  itemPriceInput.value='';
  itemCategorySelect.value='';
  editingItemId=null;
  itemFormTitle.textContent='Add Item';
  saveItemBtn.textContent='Add Item';
  cancelItemBtn.style.display='none';
}

function deleteItem(id){
  if(!confirm('Delete this item?')) return;
  fetch('../api/items/delete_item.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id})
  }).then(r=>r.json())
    .then(data=>{ if(data.status==='success'){ loadItems(); } else alert(data.message); });
}
// ---------- ADD-ONS GLOBALS ----------
let editingAddonId = null;
const addonTableBody = document.querySelector('#addonTable tbody');
const addonNameInput = document.getElementById('addonName');
const addonPriceInput = document.getElementById('addonPrice');
const addonFormTitle = document.getElementById('addonFormTitle');
const saveAddonBtn = document.getElementById('saveAddonBtn');
const cancelAddonBtn = document.getElementById('cancelAddonBtn');

// ---------- LOAD ADD-ONS ----------
function loadAddons(){
  fetch('../api/addons/read_all_addons.php')
    .then(r=>r.json())
    .then(data=>{
      if(data.status==='success'){
        addonTableBody.innerHTML='';
        data.data.forEach(addon=>{
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${addon.id}</td>
            <td>${addon.name}</td>
            <td>₱${parseFloat(addon.price).toFixed(2)}</td>
            <td>
              <button onclick="viewAddon(${addon.id})">View</button>
              <button onclick="editAddon(${addon.id})">Edit</button>
              <button onclick="deleteAddon(${addon.id})">Delete</button>
            </td>
          `;
          addonTableBody.appendChild(tr);
        });
      }
    });
}

// ---------- ADD-ONS ACTIONS ----------
function viewAddon(id){
  fetch(`../api/addons/read_one_addons.php?id=${id}`)
    .then(r=>r.json())
    .then(data=>{
      if(data.status==='success'){
        alert(`Add-On ID: ${data.data.id}\nName: ${data.data.name}\nPrice: ₱${data.data.price}`);
      } else alert(data.message);
    });
}

function editAddon(id){
  fetch(`../api/addons/read_one_addons.php?id=${id}`)
    .then(r=>r.json())
    .then(data=>{
      if(data.status==='success'){
        addonNameInput.value = data.data.name;
        addonPriceInput.value = parseFloat(data.data.price);
        editingAddonId = data.data.id;
        addonFormTitle.textContent='Edit Add-On';
        saveAddonBtn.textContent='Update Add-On';
        cancelAddonBtn.style.display='inline-block';
      }
    });
}

saveAddonBtn.addEventListener('click', ()=>{
  const name = addonNameInput.value.trim();
  const price = parseFloat(addonPriceInput.value);
  if(!name || isNaN(price)){ alert('Fill all fields'); return; }

  const url = editingAddonId ? '../api/addons/edit_addons.php' : '../api/addons/create_addons.php';
  const payload = editingAddonId ? {id:editingAddonId,name,price} : {name,price};

  fetch(url,{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify(payload)
  }).then(r=>r.json())
    .then(data=>{
      if(data.status==='success'){ resetAddonForm(); loadAddons(); } else alert(data.message);
    });
});

cancelAddonBtn.addEventListener('click', resetAddonForm);

function resetAddonForm(){
  addonNameInput.value='';
  addonPriceInput.value='';
  editingAddonId=null;
  addonFormTitle.textContent='Add Add-On';
  saveAddonBtn.textContent='Add Add-On';
  cancelAddonBtn.style.display='none';
}

function deleteAddon(id){
  if(!confirm('Delete this add-on?')) return;
  fetch('../api/addons/delete_addons.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id})
  }).then(r=>r.json())
    .then(data=>{ if(data.status==='success'){ loadAddons(); } else alert(data.message); });
}

const sizeTableBody = document.querySelector('#sizeTable tbody');
const sizeNameInput = document.getElementById('sizeName');
const sizePriceInput = document.getElementById('sizePrice');
const sizeFormTitle = document.getElementById('sizeFormTitle');
const saveSizeBtn = document.getElementById('saveSizeBtn');
const cancelSizeBtn = document.getElementById('cancelSizeBtn');

// ---------- LOAD SIZES ----------
function loadSizes() {
  fetch('../api/size/read_all_size.php')
    .then(r => r.json())
    .then(data => {
      if (data.status === 'success') {
        sizeTableBody.innerHTML = '';
        data.data.forEach(size => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${size.id}</td>
            <td>${size.name}</td>
            <td>₱${parseFloat(size.price_modifier).toFixed(2)}</td>
            <td>
              <button onclick="viewSize(${size.id})">View</button>
              <button onclick="editSize(${size.id})">Edit</button>
              <button onclick="deleteSize(${size.id})">Delete</button>
            </td>
          `;
          sizeTableBody.appendChild(tr);
        });
      }
    });
}

// ---------- SIZE ACTIONS ----------
function viewSize(id) {
  fetch(`../api/size/read_one_size.php?id=${id}`)
    .then(r => r.json())
    .then(data => {
      if (data.status === 'success') {
        alert(`Size ID: ${data.data.id}\nName: ${data.data.name}\nPrice: ₱${parseFloat(data.data.price_modifier).toFixed(2)}`);
      } else {
        alert(data.message);
      }
    });
}

function editSize(id) {
  fetch(`../api/size/read_one_size.php?id=${id}`)
    .then(r => r.json())
    .then(data => {
      if (data.status === 'success') {
        sizeNameInput.value = data.data.name;
        sizePriceInput.value = parseFloat(data.data.price_modifier);
        editingSizeId = data.data.id;
        sizeFormTitle.textContent = 'Edit Size';
        saveSizeBtn.textContent = 'Update Size';
        cancelSizeBtn.style.display = 'inline-block';
      } else {
        alert(data.message);
      }
    });
}

saveSizeBtn.addEventListener('click', () => {
  const name = sizeNameInput.value.trim();
  const price = parseFloat(sizePriceInput.value);

  if (!name || isNaN(price)) {
    alert('Fill all fields');
    return;
  }

  const url = editingSizeId ? '../api/size/edit_size.php' : '../api/size/create_size.php';
  const payload = editingSizeId ? {id: editingSizeId, name, price_modifier: price} : {name, price_modifier: price};

  fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(payload)
  })
  .then(r => r.json())
  .then(data => {
    if (data.status === 'success') {
      resetSizeForm();
      loadSizes();
    } else {
      alert(data.message);
    }
  });
});

cancelSizeBtn.addEventListener('click', resetSizeForm);

function resetSizeForm() {
  sizeNameInput.value = '';
  sizePriceInput.value = '';
  editingSizeId = null;
  sizeFormTitle.textContent = 'Add Size';
  saveSizeBtn.textContent = 'Add Size';
  cancelSizeBtn.style.display = 'none';
}

function deleteSize(id) {
  if (!confirm('Delete this size?')) return;

  fetch('../api/size/delete_size.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({id})
  })
  .then(r => r.json())
  .then(data => {
    if (data.status === 'success') {
      loadSizes();
    } else {
      alert(data.message);
    }
  });
}

// ---------- INITIAL LOAD ----------
loadSizes();
loadAddons();
loadCategories();
loadItems();
