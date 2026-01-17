async function fetchOrders() {
  try {
    const response = await fetch('../api/read_all/read_all.php'); // adjust path if needed
    const data = await response.json();

    const container = document.getElementById('ordersContainer');

    if(data.status !== 'success') {
      container.innerHTML = "<p>Error fetching orders</p>";
      return;
    }

    if(data.data.length === 0) {
      container.innerHTML = "<p>No orders found.</p>";
      return;
    }

    let html = '<table>';
    html += `
      <tr>
        <th>Order ID</th>
        <th>Customer</th>
        <th>Total Price</th>
        <th>Created At</th>
        <th>Items</th>
      </tr>
    `;

    data.data.forEach(order => {
      let itemsHtml = '<table class="items-table">';
      itemsHtml += `<tr>
                      <th>Item Name</th>
                      <th>Quantity</th>
                      <th>Size</th>
                      <th>Addons</th>
                      <th>Price</th>
                    </tr>`;

      order.items.forEach(item => {
        itemsHtml += `<tr>
                        <td>${item.item_name}</td>
                        <td>${item.quantity}</td>
                        <td>${item.size || '-'}</td>
                        <td>${item.addons || '-'}</td>
                        <td>${item.item_price}</td>
                      </tr>`;
      });

      itemsHtml += '</table>';

      html += `<tr>
                  <td>${order.id}</td>
                  <td>${order.customer_name}</td>
                  <td>${order.total_price}</td>
                  <td>${order.created_at}</td>
                  <td>${itemsHtml}</td>
               </tr>`;
    });

    html += '</table>';

    container.innerHTML = html;

  } catch (err) {
    console.error(err);
    document.getElementById('ordersContainer').innerHTML = "<p>Error loading orders.</p>";
  }
}

// Call the function
fetchOrders();
