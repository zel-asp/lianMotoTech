//Search functionality
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.querySelector('.Search');
  const searchButton = document.querySelector('.btn-dark');
  const motorParts = document.querySelectorAll('#MotorParts .card');

  function filterParts() {
    const searchTerm = searchInput.value.toLowerCase();

    motorParts.forEach(part => {
      const title = part.querySelector('.card-title').textContent.toLowerCase();
      const description = part.querySelector('.card-text').textContent.toLowerCase();

      if (title.includes(searchTerm) || description.includes(searchTerm)) {
        part.parentElement.style.display = 'block';
      } else {
        part.parentElement.style.display = 'none';
      }
    });
  }

  // Add event listeners for both button click and Enter key
  searchInput.addEventListener('input', filterParts);

  searchButton.addEventListener('click', filterParts);
  searchInput.addEventListener('keyup', function (e) {
    if (e.key === 'Enter') {
      filterParts();
    }
  });
});



//for admin dashboard


// Toggle sidebar on mobile
document.getElementById('menuToggle').addEventListener('click', function () {
  document.getElementById('sidebar').classList.add('active');
});

document.getElementById('closeBtn').addEventListener('click', function () {
  document.getElementById('sidebar').classList.remove('active');
});

// Toggle add product form
document.getElementById('addProductBtn').addEventListener('click', function () {
  document.getElementById('productList').style.display = 'none';
  document.getElementById('Add_product').style.display = 'block';
});

document.getElementById('cancelProductForm').addEventListener('click', function () {
  document.getElementById('productList').style.display = 'block';
  document.getElementById('Add_product').style.display = 'none';
});

// Section navigation
document.querySelectorAll('.menu-item').forEach(item => {
  item.addEventListener('click', function (e) {
    e.preventDefault();

    // Update active menu item
    document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
    this.classList.add('active');

    // Show the selected section
    const sectionId = this.getAttribute('data-section');
    document.querySelectorAll('.section').forEach(section => {
      section.classList.remove('active');
    });
    document.getElementById(sectionId + '-section').classList.add('active');

    // Close sidebar on mobile
    if (window.innerWidth < 992) {
      document.getElementById('sidebar').classList.remove('active');
    }
  });
});

// Handle hash on page load
window.addEventListener('load', function () {
  if (window.location.hash) {
    const sectionId = window.location.hash.substring(1);
    const menuItem = document.querySelector(`.menu-item[data-section="${sectionId}"]`);
    if (menuItem) {
      menuItem.click();
    }
  }
});


//edit button
document.getElementById('js-edit_Info').addEventListener('click', function () {
  document.getElementById('js-change_adminInfo').classList.remove('d-none');
  document.querySelector('.profile-content').classList.add('d-none');
});

document.getElementById('js-cancelEdit').addEventListener('click', function () {
  document.getElementById('js-change_adminInfo').classList.add('d-none');
  document.querySelector('.profile-content').classList.remove('d-none');
  const form = document.querySelector('#js-change_adminInfo form');
  if (form) {
    form.reset();
  }
});


