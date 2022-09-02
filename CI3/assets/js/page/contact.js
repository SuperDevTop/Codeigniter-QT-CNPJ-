/*************** TO DO **********************/
"use strict";
$(document).ready(() => {

  $('#myForm').validate({
    rules: {
      name: {
        required: true,
      },
      email: {
        required: true,
        email: true
      },
      phone: {
        required: true,
        digits: true
      },
      address: {
        required: true,
      }
    },
    messages: {
      title: {
        required: "Please provide name",
      },
      email: {
        required: "Please provide valid email",
      },
      phone: {
        required: "Please provide valid number",
      },
      address: {
        required: "Please provide address",
      },
    },
    submitHandler: function (form) {
      const modifiedData = {};
      modifiedData.img = selected_img;
      $.each($("#contactForm input"), function () {
        Object.assign(modifiedData, {
          [$(this).attr("data-src")]: $(this).val(),
        });
      });
      $.each($("#contactForm textarea"), function () {
        Object.assign(modifiedData, {
          [$(this).attr("data-src")]: $(this).val(),
        });
      });

      if (isEditClick) {
        var rowIndex = $("#contactForm").attr("rowindex");
        console.log(modifiedData);

        dataTable.row(rowIndex).data(modifiedData).draw();
      } else {
        console.log(modifiedData);
        dataTable.row.add(modifiedData).draw();
      }

      $("#contactForm").modal("toggle");
    }
  });


  $(".contact-list a").on("click", function () {
    $(".contact-list a").removeClass("active");
    $(this).addClass("active");
    $(".contact-item").hide();
    $("." + $(this).data("type")).show(10);

    return false;
  });
  var anchors = $(".contact-list li").click(function () {
    //savesubcat()
    $(this).addClass("active");
    anchors.not(this).removeClass("active");
  });
  //data table initialization
  var selected_img;
  var isEditClick = false;
  const dataTable = $("#contactTbl").DataTable({
    ajax: {
      url: "assets/js/data/contact.json",
      dataSrc: function (json) {
        return json.data;
      },
    },
    dom: "rtip",
    language: {
      searchPlaceholder: "Search contacts",
      search: "",
    },
    columns: [{
        data: "img",
        render: function (data, type, row) {
          return '<img class="contact-img" src="' + data + '" />';
        },
      },
      {
        data: "name",
      },
      {
        data: "email",
      },
      {
        data: "phone",
      },
      {
        data: "address",
      },
      {
        data: "action",
        render: function (data, type, full, meta) {
          return (
            '<a class="btn-view"><i class="far fa-edit m-r-10"></i></a>' +
            '<a class="btn-delete"><i class="far fa-trash-alt"></i></a>'
          );
        },
      },
    ],
    columnDefs: [{
      targets: 0,
      searchable: false,
      orderable: false,
      className: "dt-body-center",
      render: function (data, type, full, meta) {
        return (
          '<input type="checkbox" name="id[]" value="' +
          $("<div/>").text(data).html() +
          '">'
        );
      },
    }, ],
    order: [1, "asc"],
  });
  $("#searchItem").keyup(function () {
    dataTable.search($(this).val()).draw();
  });
  $("#all").click(function (e) {
    dataTable.ajax.url("assets/js/data/contact.json").load();
    e.preventDefault();
  });
  $("#starred").click(function (e) {
    dataTable.ajax.url("assets/js/data/contact-star.json").load();
    e.preventDefault();
  });
  $("#frequently").click(function (e) {
    dataTable.ajax.url("assets/js/data/contact-freq.json").load();
    e.preventDefault();
  });
  $("#trashed").click(function (e) {
    dataTable.ajax.url("assets/js/data/contact-trashed.json").load();
    e.preventDefault();
  });

  $("#addBtn").click(function () {
    isEditClick = false;
    selected_img = "assets/img/users/user-1.png";
    $("#contactForm").modal("toggle");
  });

  // Handle click on "Delete" button
  $("#contactTbl tbody").on("click", ".btn-delete", function (e) {
    swal({
      title: "Are you sure?",
      text: "Once deleted, you will not be able to recover this contact!",
      buttons: true,
    }).then((willDelete) => {
      if (willDelete) {
        dataTable.row($(this).parents("tr")).remove().draw();
      } else {}
    });
  });
  //edit button handler (open up edit form modal)
  $("#contactTbl").on("click", ".btn-view", function () {
    isEditClick = true;
    //get clicked row
    const rowClicked = dataTable.row($(this).closest("tr"));

    var data_row = dataTable.row($(this).closest("tr")).data();
    selected_img = data_row.img;

    //populate edit form with row data by corresponding
    //rowClicked property based on 'data-src' attribute
    $.each($("#contactForm input"), function () {
      $(this).val(rowClicked.data()[$(this).attr("data-src")]);
    });
    $.each($("#contactForm textarea"), function () {
      $(this).val(rowClicked.data()[$(this).attr("data-src")]);
    });
    //set modal attribute rowindex to corresponding row index
    $("#contactForm").attr("rowindex", rowClicked.index());
    //open up edit form modal
    $("#contactForm").modal("toggle");
  });
  $("#contactForm").on("hidden.bs.modal", function () {
    $(this).find("form").trigger("reset");
  });
});