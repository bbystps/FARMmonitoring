<!DOCTYPE html>
<html lang="en">
  
<?php 
  // session_start();
  // if($_SESSION['loggedin'] !== true){
  //   header("Location: ../login");
  //   exit();
  // }
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RHT Monitoring System</title>
  
  <link rel="stylesheet" href="../css/montserrat.css">
  <link rel="stylesheet" href="../css/icon.css">
  <link rel="stylesheet" href="../css/element.css">
  <link rel="stylesheet" href="../css/style.css">
  <!-- <link rel="stylesheet" href="../datatables/css/dataTables.dataTables.min.css"> -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  
</head>
<body>
  
<div class="container">

  <div id="nav-hidden">
    <div class="container-space-between">
      <!-- Toggle button -->
      <!-- <div class="toggle-button">☰</div> -->
      <div class="toggle-button">&#9776;</div>
      <div class="title-nav-hidden">FARM Monitoring System</div>
      <div class="user-button">
        <div class="user-container">
          <div class="user-text">
            Admin
            <i class="icon-user"></i> 
            <div class="logout-button" onclick="logout();">Logout</div> <!-- Logout button -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- user - put top details here -->
  <div id="user">
    <div class="user-container">
      <div class="user-text">
        Admin
        <i class="icon-user"></i> 
        <div class="logout-button" onclick="logout();">Logout</div> <!-- Logout button -->
      </div>
    </div>
  </div>
  
  <div id="nav">
    <div class="nav-container">
      <div class="nav-lists nav-active" onclick="gotoEnvSoil(this);">Environment & Soil</div>
      <div class="nav-lists" onclick="gotoWater(this);">Water</div>
      <button onclick="fetchLatestData()">Fetch Latest Data</button>
    </div>
  </div>

  <!-- sidebar start -->
  <div id="sidebar">
    <?php include("../includes/sidebar.php"); ?>
  </div>
  <!-- sidebar end -->

  <!-- content1 - datatable for sensors val -->
  <div id="content1">
    <div class="content1-container">
      <!-- <div class="content1-title">Historical Values</div> -->
      <!-- <div class="content1-label">Historical Values - <span id="HV_display"></span></div> -->
      <div id="chartdiv">chart placed here</div>
    </div>
  </div>

  <div id="content2">
    <div class="content2-container">
      <!-- <div class="content2-card" id="card1">
        <div class="content2-card-num" id="temperature">23</div>
        <div class="content2-card-text">
          <div class="content2-card-text-up">°C</div>
          <div class="content2-card-text-down">Temperature</div>
        </div>
      </div>
      <div class="content2-card" id="card2">
        <div class="content2-card-num" id="humidity">51</div>
        <div class="content2-card-text">
          <div class="content2-card-text-up">%</div>
          <div class="content2-card-text-down">Humidity</div>
        </div>
      </div>
      <div class="content2-card" id="card3">
        <div class="content2-card-num" id="heat_index">0.7</div>
        <div class="content2-card-text">
          <div class="content2-card-text-up">°C</div>
          <div class="content2-card-text-down">Heat Index</div>
        </div>
      </div>
      <div class="content2-card" id="card4">
        <div class="content2-card-num" id="soil_moisture">2</div>
        <div class="content2-card-text">
          <div class="content2-card-text-up">wfv</div>
          <div class="content2-card-text-down">Soil Moisture</div>
        </div>
      </div>
      <div class="content2-card" id="card5">
        <div class="content2-card-num" id="uv_level">4</div>
        <div class="content2-card-text">
          <div class="content2-card-text-up">mW/cm2</div>
          <div class="content2-card-text-down">UV Level</div>
        </div>
      </div> -->
    </div>
  </div>
  
  <div id="content3">
    <div class="content3-container">
      <div class="content2-card" id="cardA">
        <div class="content2-card-num" id="nitrogen">--</div>
        <div class="content2-card-text">
          <div class="content2-card-text-up" id="nitrogen_unit">mW/cm2</div>
          <div class="content2-card-text-down" id="nitrogen_label">Nitrogen</div>
        </div>
      </div>
      <div class="content2-card" id="cardB">
        <div class="content2-card-num" id="phosphorus">--</div>
        <div class="content2-card-text">
          <div class="content2-card-text-up" id="phosphorus_unit">mW/cm2</div>
          <div class="content2-card-text-down" id="phosphorus_label">Phosphorus</div>
        </div>
      </div>
      <div class="content2-card" id="cardC">
        <div class="content2-card-num" id="potassium">--</div>
        <div class="content2-card-text">
          <div class="content2-card-text-up" id="potassium_unit">mW/cm2</div>
          <div class="content2-card-text-down" id="potassium_label">Potassium</div>
        </div>
      </div>
      <div class="content2-card" id="cardD">
        <div class="content2-card-num" id="ph_level">--</div>
        <div class="content2-card-text">
          <div class="content2-card-text-up" id="ph_level_unit">mW/cm2</div>
          <div class="content2-card-text-down" id="ph_level_label">pH Level</div>
        </div>
      </div> 
    </div>
  </div>

</div>

<?php include("modal.php"); ?>
<?php include("../includes/admin_modal.php"); ?>

</body>

<script src="../js/jquery.min.js"></script>

<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

<?php //include("script_table.php"); ?>
<?php //include("script_gauge.php"); ?>
<?php include("script_chart.php"); ?>
<?php include("script_cards.php"); ?>
<?php include("script_modal.php"); ?>

<script>
const userTexts = document.querySelectorAll('.user-text');
const logoutButtons = document.querySelectorAll('.logout-button');
// Add click event listeners to each user text
userTexts.forEach((userText, index) => {
  userText.addEventListener('click', (event) => {
    // Prevent the event from bubbling up to the document
    event.stopPropagation();
    
    // Toggle the corresponding logout button
    logoutButtons[index].style.display = (logoutButtons[index].style.display === 'block') ? 'none' : 'block';
  });
});
// Hide the logout button if clicking outside
document.addEventListener('click', () => {
  logoutButtons.forEach(button => {
    button.style.display = 'none';
  });
});
</script>

<script>
$(document).ready(function() {
  loadSidebarSensor();
});

function loadSidebarSensor() {
  
  var activeDiv = document.querySelector('.nav-lists.nav-active');
  var activeDivText = activeDiv.textContent || activeDiv.innerText;
  // console.log(activeDivText);

  if (activeDivText === "Environment & Soil") {
    url = 'fetch_sensor_list.php?nav=sensor_env_soil';
    console.log(url);
  } else if (activeDivText === "Water") {
    url = 'fetch_sensor_list.php?nav=sensor_water';
    console.log(url);
  }

  $.ajax({
    url: url, // Path to your PHP script
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      // Clear the existing content
      $('.site-lists').empty();

      // Variable to keep track of the first occurrence
      let isFirstOccurrence = true;

      // Loop through the response and append to the site-lists div
      response.forEach(function(location) {
        // If it's the first occurrence, mark it as active
        const isActive = isFirstOccurrence ? ' active' : '';
        
        // Append the location to the site-lists div
        $('.site-lists').append(
          '<p><i class="icon-location"></i><span class="site-location' + isActive + '" data-region="' + location.sensor_id + '">' + location.sensor_id + '</span></p>'
        );
        
        // Set isFirstOccurrence to false after the first item
        isFirstOccurrence = false;
      });

      // Add click event listener to change active region
      $('.site-lists').on('click', '.site-location', function() {
        // Remove active class from all regions
        $('.site-location').removeClass('active');
        
        // Add active class to the clicked region
        $(this).addClass('active');
        
        // Optionally, handle the change event for the selected region
        const selectedRegion = $(this).data('region');
        console.log('Selected region:', selectedRegion);

        updateChart();
        updateCards();
        // Add your logic here to handle the selected region
      });
      
      updateChart();
      updateCards();
    },
    error: function(xhr, status, error) {
      console.error('AJAX Error:', status, error);
    }
  });

}
</script>


<script>
  function clearActiveClass() {
    // Remove 'nav-active' class from all elements with the 'nav-lists' class
    const navItems = document.querySelectorAll('.nav-lists');
    navItems.forEach(item => item.classList.remove('nav-active'));
  }

  function gotoEnvSoil(element) {
    clearActiveClass(); // Clear active class from all nav items
    element.classList.add('nav-active'); // Add 'nav-active' class to the clicked element
    // Your logic for navigating to Environment & Soil
    console.log("Navigating to Environment & Soil");
    loadSidebarSensor();
  }

  function gotoWater(element) {
    clearActiveClass(); // Clear active class from all nav items
    element.classList.add('nav-active'); // Add 'nav-active' class to the clicked element
    // Your logic for navigating to Water
    console.log("Navigating to Water");
    loadSidebarSensor();
  }
</script>

<!-- Sidebar toggle button open close -->
<script>
document.querySelector('.toggle-button').addEventListener('click', function() {
  const sidebar = document.getElementById('sidebar');
  sidebar.classList.add("sidebar-visible");
  sidebar.style.display = 'block';
});
document.querySelector('.close-sidebar').addEventListener('click', function() {
  const sidebar = document.getElementById('sidebar');
  sidebar.classList.remove("sidebar-visible");
  sidebar.style.display = 'none';
});
window.addEventListener('resize', function() {
  const sidebar = document.getElementById('sidebar');
  if (window.innerWidth > 550) {
    // Automatically hide the sidebar on larger screens
    sidebar.classList.remove("sidebar-visible");
    sidebar.style.display = 'block';
  } else if (!sidebar.classList.contains("sidebar-visible")) {
    sidebar.style.display = 'none';
  }
});
</script>


<script>
  function gotoDashboard(){window.location.replace("../dashboard");}
  function gotoDevices(){window.location.replace("../devices");}
  function logout(){window.location.replace("../login/logout.php");}
</script>
</html>
