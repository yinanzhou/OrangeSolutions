function setUserStatus(uid, enabled) {
  if (!confirm('Confirm to ' + (enabled?"enable":"disable") + ' user ' + uid)) {
    return;
  }
  $.ajax({
    type: "POST",
    url: '/admin/users/set_user_status',
    data: {
      user_id: uid,
      user_enabled: enabled
    },
    success: function(data) {
      alert(data);
      location.reload();
    },
    dataType: "json"
  });
}

function removeMembership(uid, gid) {
  if (!confirm('Confirm to remove membership?')) {
    return;
  }
  $.ajax({
    type: "POST",
    url: '/admin/users/remove_user_membership',
    data: {
      user_id: uid,
      group_id: gid
    },
    success: function(data) {
      alert(data);
      location.reload();
    },
    dataType: "json"
  });
}
