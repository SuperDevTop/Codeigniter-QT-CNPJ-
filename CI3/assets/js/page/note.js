$(function () {
  function removeNote() {
    $(".remove-note")
      .off("click")
      .on("click", function (event) {
        event.stopPropagation();
        swal({
          title: "Are you sure?",
          text: "Once deleted, you will not be able to recover this note!",
          buttons: true,
        }).then((willDelete) => {
          if (willDelete) {
            $(this).parents(".single-note-item").remove();
          } else {
          }
        });
      });
  }

  function favouriteNote() {
    $(".favourite-note")
      .off("click")
      .on("click", function (event) {
        event.stopPropagation();
        $(this).parents(".single-note-item").toggleClass("note-favourite");
      });
  }

  function addLabelGroups() {
    $(".category-selector .badge-group-item")
      .off("click")
      .on("click", function (event) {
        event.preventDefault();
        /* Act on the event */
        var getclass = this.className;
        var getSplitclass = getclass.split(" ")[0];
        if ($(this).hasClass("badge-work")) {
          $(this).parents(".note-card-body").removeClass("note-bg-red");
          $(this).parents(".note-card-body").removeClass("note-bg-green");
          $(this).parents(".note-card-body").toggleClass("note-bg-purple");
          $(this).parents(".single-note-item").removeClass("note-family");
          $(this).parents(".single-note-item").removeClass("note-important");
          $(this).parents(".single-note-item").toggleClass(getSplitclass);
        } else if ($(this).hasClass("badge-family")) {
          $(this).parents(".note-card-body").removeClass("note-bg-red");
          $(this).parents(".note-card-body").removeClass("note-bg-purple");
          $(this).parents(".note-card-body").toggleClass("note-bg-green");
          $(this).parents(".single-note-item").removeClass("note-work");
          $(this).parents(".single-note-item").removeClass("note-important");
          $(this).parents(".single-note-item").toggleClass(getSplitclass);
        } else if ($(this).hasClass("badge-important")) {
          $(this).parents(".note-card-body").removeClass("note-bg-green");
          $(this).parents(".note-card-body").removeClass("note-bg-purple");
          $(this).parents(".note-card-body").toggleClass("note-bg-red");
          $(this).parents(".single-note-item").removeClass("note-family");
          $(this).parents(".single-note-item").removeClass("note-work");
          $(this).parents(".single-note-item").toggleClass(getSplitclass);
        }
      });
  }

  var $btns = $(".note-link").click(function () {
    if (this.id == "all-category") {
      var $el = $("." + this.id).fadeIn();
      $("#note-full-container > div").not($el).hide();
    }
    if (this.id == "important") {
      var $el = $("." + this.id).fadeIn();
      $("#note-full-container > div").not($el).hide();
    } else {
      var $el = $("." + this.id).fadeIn();
      $("#note-full-container > div").not($el).hide();
    }
    $btns.removeClass("active");
    $(this).addClass("active");
  });

  $("#add-notes").on("click", function (event) {
    $("#addnotesmodal").modal("show");
    $("#btn-n-save").hide();
    $("#btn-n-add").show();
  });

  // Button add
  $("#btn-n-add").on("click", function (event) {
    event.preventDefault();
    /* Act on the event */
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, "0");
    var mm = String(today.getMonth()); //January is 0!
    var yyyy = today.getFullYear();
    var monthNames = [
      "Jan",
      "Feb",
      "Mar",
      "Apr",
      "May",
      "Jun",
      "Jul",
      "Aug",
      "Sep",
      "Oct",
      "Nov",
      "Dec",
    ];
    today = dd + " " + monthNames[mm] + " " + yyyy;

    var $_noteTitle = document.getElementById("note-has-title").value;
    var $_noteDescription = document.getElementById(
      "note-has-description"
    ).value;

    $html =
      '<div class="col-md-4 single-note-item all-category note-family">' +
      '<div class="note-card note-card-body note-bg-green">' +
      '<span class="side-stick"></span>' +
      '<div class="note-header">' +
      '<h5 class="note-title text-truncate w-75 mb-0" data-noteHeading="' +
      $_noteTitle +
      '">' +
      $_noteTitle +
      "</h5>" +
      '<div class="text-right w-25">' +
      '<span class="mr-1 "><i class="far fa-star favourite-note font-17"></i></span>' +
      '<span class="mr-1"><i class="far fa-trash-alt remove-note font-17"></i></span>' +
      "</div>" +
      "</div>" +
      '<p class="note-date font-12">' +
      today +
      "</p>" +
      '<div class="note-content">' +
      '<p class="note-inner-content"' +
      'data-notecontent="' +
      $_noteDescription +
      '">' +
      $_noteDescription +
      "</p>" +
      "</div>" +
      '<div class="d-flex align-items-center">' +
      '<ul class="list-unstyled order-list m-b-0">' +
      '<li class="team-member team-member-sm"><img class="rounded-circle"' +
      'src="assets/img/users/user-8.png" alt="user" data-toggle="tooltip" title=""' +
      'data-original-title="Wildan Ahdian"></li>' +
      '<li class="team-member team-member-sm"><img class="rounded-circle"' +
      'src="assets/img/users/user-9.png" alt="user" data-toggle="tooltip" title=""' +
      'data-original-title="John Deo"></li>' +
      '<li class="team-member team-member-sm"><img class="rounded-circle"' +
      'src="assets/img/users/user-10.png" alt="user" data-toggle="tooltip" title=""' +
      'data-original-title="Sarah Smith"></li>' +
      '<li class="avatar avatar-sm"><span class="badge badge-primary">+4</span></li>' +
      "</ul>" +
      '<div class="ms-auto">' +
      '<div class="category-selector btn-group">' +
      '<a class="nav-link dropdown-toggle category-dropdown label-group p-0"' +
      'data-toggle="dropdown" href="#" role="button" aria-haspopup="true"' +
      'aria-expanded="true">' +
      '<div class="category">' +
      '<div class="category-work"></div>' +
      '<div class="category-family"></div>' +
      '<div class="category-important"></div>' +
      '<span class="more-options text-dark"><i class="icon-options-vertical"></i></span>' +
      "</div>" +
      "</a>" +
      '<div class="dropdown-menu dropdown-menu-right category-menu">' +
      '<a class="note-work badge-group-item badge-work dropdown-item position-relative category-work"' +
      'href="javascript:void(0);">' +
      '<i class="far fa-dot-circle mr-2"></i>Work' +
      "</a>" +
      '<a class="note-family badge-group-item badge-family dropdown-item position-relative category-family"' +
      'href="javascript:void(0);">' +
      '<i class="far fa-dot-circle mr-2"></i> Family' +
      "</a>" +
      '<a class="note-important badge-group-item badge-important dropdown-item position-relative category-important"' +
      'href="javascript:void(0);">' +
      '<i class="far fa-dot-circle mr-2"></i> Important' +
      "</a>" +
      "</div>" +
      "</div>" +
      "</div>" +
      "</div>" +
      "</div>" +
      "</div>";

    $("#note-full-container").prepend($html);
    $("#addnotesmodal").modal("hide");

    removeNote();
    favouriteNote();
    addLabelGroups();
  });

  $("#addnotesmodal").on("hidden.bs.modal", function (event) {
    event.preventDefault();
    document.getElementById("note-has-title").value = "";
    document.getElementById("note-has-description").value = "";
  });

  removeNote();
  favouriteNote();
  addLabelGroups();

  $("#btn-n-add").attr("disabled", "disabled");
});

$("#note-has-title").keyup(function () {
  var empty = false;
  $("#note-has-title").each(function () {
    if ($(this).val() == "") {
      empty = true;
    }
  });

  if (empty) {
    $("#btn-n-add").attr("disabled", "disabled");
  } else {
    $("#btn-n-add").removeAttr("disabled");
  }
});
