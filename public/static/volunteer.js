var volunteer_available;
var ringing = false;
var last_ring_service_request_id;

function toogleAvailability() {
  volunteer_available = !volunteer_available;
  $.post("/volunteer/availability/" + (volunteer_available?1:0) , function( data ) {
    status_update();
  });
}

function close_ringing() {
  $(".ring-screen").remove();
  ringing = false;
}

function ring(service_request_id) {
  ringing = true;
  last_ring_service_request_id = service_request_id;
  $("body").prepend('<iframe src="/volunteer/ring/' + service_request_id + '" class="ring-screen"></iframe>');
}

function update_service_request_status(id,status) {
  $.ajax({
    type: "POST",
    url: "/volunteer/service-requests/" + id,
    data: {service_request_status: status}
  });
}

function status_update() {
  $.get("/volunteer/status", function(data) {
    volunteer_available = data.availability;
    if (data.pending_service_request_id) {
      if(data.pending_service_request_id != last_ring_service_request_id){
        if (ringing) {
          update_service_request_status(data.pending_service_request_id, "Rejected (Volunteer Busy)");
        } else {
          ring(data.pending_service_request_id);
        }
      }
    }
    updateAvailabilityDisplay()
  });
}

function updateAvailabilityDisplay() {
  $("#volunteer-availability-display")
      .removeClass("badge-warning")
      .removeClass("badge-success")
      .removeClass("badge-danger");
  if (volunteer_available) {
    $("#volunteer-availability-display").addClass("badge-success").text("Ready for Service");
  } else {
    $("#volunteer-availability-display").addClass("badge-danger").text("Unavailable");
  }
}

status_update();
setInterval(status_update, 5000);
