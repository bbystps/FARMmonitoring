
<!-- Modal Script Start -->
<script>

function openSuccessModal() {
  const successModal = document.getElementById('successModal');
  successModal.style.display = 'flex';
}
function closeSuccessModal() {
  const successModal = document.getElementById('successModal');
  successModal.style.display = 'none';
}
function openErrorModal() {
  const errorModal = document.getElementById('errorModal');
  errorModal.style.display = 'flex';
}
function closeErrorModal() {
  const errorModal = document.getElementById('errorModal');
  errorModal.style.display = 'none';
}

function openAddDeviceModal() {
  $("#NEWdeviceFORM")[0].reset();
  const AddDeviceModal = document.getElementById('AddDeviceModal');
  AddDeviceModal.style.display = 'flex';
}
function closeAddDeviceModal() {
  const AddDeviceModal = document.getElementById('AddDeviceModal');
  AddDeviceModal.style.display = 'none';
}

function confirmAddDeviceModal(){
  console.log("Adding New Device");
  var form = $('#NEWdeviceFORM')[0];
  var data = new FormData(form);
  //data.append('EMP_ID', document.getElementById('session_id').value);
  $.ajax({
    type: "POST",
    enctype: 'multipart/form-data',
    url:"add_new_device.php",
    data:data,
    processData: false,
    contentType: false,
    cache: false,
    success:function(data){
      try {
      // If the data is already a JSON object, you don't need to parse it.
      // But if it's a string, you can parse it using JSON.parse()
      var responseData = typeof data === 'string' ? JSON.parse(data) : data;

      // Access the parsed data
      if(responseData.status === 'success'){
        console.log(responseData.message); // Outputs: Device added and tables created successfully
        closeAddDeviceModal();
        $("#promptSuccessSM").text("Device Added Successfully.");
        openSuccessModal();
        loadSidebarSensor();
      } else {
        console.log("Error:", responseData.message);
        closeAddDeviceModal();
        $("#promptErrorSM").text(responseData.message);
        openErrorModal();
      }
    } catch (e) {
      console.error("Parsing error:", e);
      closeAddDeviceModal();
      $("#promptErrorSM").text("Failed to Add Device");
      openErrorModal();
    }
    },
    error: function(xhr, status, error) {
    console.error(xhr.responseText);
    $("#promptErrorSM").text("Failed to Add Device");
    openErrorModal();
    }
  })
}

function inputIDtype() {
  var sensorType = document.getElementById("sensor_type").value;
  var sensorIdField = document.getElementById("sensor_id");
  
  if(sensorType === "sensor_env_soil") {
    sensorIdField.value = "ES";
  } else if(sensorType === "sensor_water") {
    sensorIdField.value = "W";
  } else {
    sensorIdField.value = "";
  }
}
</script>
<!-- Modal Script End -->