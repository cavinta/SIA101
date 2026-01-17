let orderItems = [];
let allAddons = [];
let allSizes = [];
let categoriesMap = {};

// ---------- LOAD CATEGORIES ----------
fetch('../api/category/read_all_category.php')
  .then(res => res.json())
  .then(data => {
    const catSelect = document.getElementById('categorySelect');
    data.data.forEach(cat => {
      const option = document.createElement('option');
      option.value = cat.id;
      option.textContent = cat.name;
      catSelect.appendChild(option);
      categoriesMap[cat.id] = cat.name;
    });
  });

// ---------- CATEGORY → LOAD ITEMS ----------
document.getElementById('categorySelect').addEventListener('change', function () {
  const categoryId = this.value;
  const itemSelect = document.getElementById('itemSelect');

  itemSelect.innerHTML = '<option value="">-- Select Item --</option>';

  if (!categoryId) return;

  fetch(`../api/items/read_all_items.php?category_id=${categoryId}`)
    .then(res => res.json())
    .then(data => {
      data.data.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = `${item.name} - ₱${item.price}`;
        option.dataset.price = item.price;
        itemSelect.appendChild(option);
      });
    });
});

// ---------- LOAD ADDONS ----------
function loadAddons() {
  const addonsDiv = document.getElementById('addonsContainer');
  addonsDiv.innerHTML = '';

  fetch('../api/addons/read_all_addons.php')
    .then(res => res.json())
    .then(data => {
      allAddons = data.data;
      data.data.forEach(addon => {
        const label = document.createElement('label');
        label.innerHTML = `
          <input type="checkbox" value="${addon.id}" data-price="${addon.price}">
          ${addon.name} (₱${addon.price})
        `;
        addonsDiv.appendChild(label);
        addonsDiv.appendChild(document.createElement('br'));
      });
    });
}

// ---------- LOAD SIZES ----------
function loadSizes() {
  const sizesDiv = document.getElementById('SizeContainer');
  sizesDiv.innerHTML = '';

  fetch('../api/size/read_all_size.php')
    .then(res => res.json())
    .then(data => {
      allSizes = data.data;
      data.data.forEach(size => {
        const label = document.createElement('label');
        label.innerHTML = `
          <input type="radio" name="size" value="${size.id}" data-price="${size.price_modifier}">
          ${size.name} (₱${size.price_modifier})
        `;
        sizesDiv.appendChild(label);
        sizesDiv.appendChild(document.createElement('br'));
      });
    });
}

// Initialize
loadAddons();
loadSizes();

// ---------- ADD TO ORDER ----------
document.getElementById('addToOrderBtn').addEventListener('click', () => {
  const customerName = document.getElementById('customerName').value.trim();
  const categoryId = document.getElementById('categorySelect').value;
  const itemSelect = document.getElementById('itemSelect');
  const itemId = itemSelect.value;
  const quantity = parseInt(document.getElementById('quantity').value);

  if (!customerName) return alert('Enter customer name');
  if (!categoryId) return alert('Select category');
  if (!itemId) return alert('Select item');

  const itemName = itemSelect.options[itemSelect.selectedIndex].text.split(' - ')[0];
  const itemPrice = parseFloat(itemSelect.options[itemSelect.selectedIndex].dataset.price);

  // --- ADDONS ---
  let selectedAddons = [];
  let addonsTotal = 0;

  document.querySelectorAll('#addonsContainer input:checked').forEach(cb => {
    const addon = allAddons.find(a => a.id == cb.value);
    if (addon) {
      selectedAddons.push({ id: addon.id, name: addon.name });
      addonsTotal += parseFloat(addon.price);
    }
  });

  // --- SIZE (single) ---
  let selectedSize = [];
  let sizeTotal = 0;

  const sizeInput = document.querySelector('#SizeContainer input[name="size"]:checked');
  if (sizeInput) {
    const size = allSizes.find(s => s.id == sizeInput.value);
    if (size) {
      selectedSize.push({ id: size.id, name: size.name });
      sizeTotal += parseFloat(size.price_modifier);
    }
  }

  const subtotal = (itemPrice + addonsTotal + sizeTotal) * quantity;

  orderItems.push({
    category: categoriesMap[categoryId],
    item_id: parseInt(itemId),
    name: itemName,
    quantity,
    addons: selectedAddons,
    size: selectedSize,
    subtotal
  });

  renderOrderTable();
  resetSelections();
});

// ---------- RENDER ORDER TABLE ----------
function renderOrderTable() {
  const tbody = document.querySelector('#orderTable tbody');
  tbody.innerHTML = '';
  let total = 0;

  orderItems.forEach((item, index) => {
    total += item.subtotal;
    const addons = item.addons.map(a => a.name).join(', ') || '-';
    const size = item.size.map(s => s.name).join(', ') || '-';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${item.category}</td>
      <td>${item.name}</td>
      <td>${addons}</td>
      <td>${size}</td>
      <td>${item.quantity}</td>
      <td>₱${item.subtotal.toFixed(2)}</td>
      <td><button onclick="removeItem(${index})">Remove</button></td>
    `;
    tbody.appendChild(tr);
  });

  document.getElementById('orderTotal').textContent = `₱${total.toFixed(2)}`;
}

// ---------- REMOVE ITEM ----------
function removeItem(index) {
  orderItems.splice(index, 1);
  renderOrderTable();
}

// ---------- RESET SELECTIONS ----------
function resetSelections() {
  document.getElementById('itemSelect').value = '';
  document.getElementById('quantity').value = 1;
  document.querySelectorAll('#addonsContainer input').forEach(cb => cb.checked = false);
  document.querySelectorAll('#SizeContainer input').forEach(rb => rb.checked = false);
}

// ---------- SUBMIT ORDER ----------
document.getElementById('submitOrderBtn').addEventListener('click', () => {
  const customerName = document.getElementById('customerName').value.trim();

  if (!customerName) return alert('Enter customer name');
  if (orderItems.length === 0) return alert('Add at least one item');

  const itemsForAPI = orderItems.map(item => ({
    item_id: item.item_id,
    quantity: item.quantity,
    addons: item.addons.map(a => a.id),
    size: item.size.map(s => s.id)
  }));

  fetch('../api/order/create_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      customer_name: customerName,
      items: itemsForAPI
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      alert(`Order saved! ID: ${data.order_id}\nTotal: ₱${data.total_price}`);
      orderItems = [];
      document.getElementById('customerName').value = '';
      renderOrderTable();
    } else {
      alert(data.message);
    }
  })
  .catch(() => alert('Failed to save order'));
});
