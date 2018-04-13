var volunteer_available;
function getAvailability() {
  $.get("/volunteer/availability", function(data) {
    if (data) {
      volunteer_available = true;
    } else {
      volunteer_available = false;
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

function toogleAvailability() {
  volunteer_available = !volunteer_available;
  $.post("/volunteer/availability/" + (volunteer_available?1:0) , function( data ) {
    getAvailability();
  });
}

function volunteer_ping() {
  if (volunteer_available) {
    $.post("/volunteer/ping");
  }
}

getAvailability();
setInterval(getAvailability, 20000);
setInterval(volunteer_ping, 10000);
