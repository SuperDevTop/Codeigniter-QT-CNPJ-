<section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-6 offset-xl-3">
            <div class="login-brand">
              VERI DESKTOP
            </div>
            <div class="card card-primary">
              <div class="card-header">
                <h4>Informe seu CNPJ</h4>
              </div>
              <div class="card-body">
                <?php echo form_open("Teste/solicitar", array('role'=>'form', 'id'=>'form-sample-1')); ?>
                  <div class="form-group">
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <div class="input-group-text">
                          <i class="fas fa-key"></i>
                        </div>
                      </div>
                      <input type="text" class="form-control" name="cnpj" autofocus placeholder="CNPJ" maxlength="14">
                    </div>
                  </div> 
                  <div class="form-group text-center"> 
                    <button class="btn btn-primary mr-2" id="button_click" onclick="executar()">Executar</button>
                    <?php echo form_submit(array('name'=>'btn_solicitar', 'id'=>'swal-2', 'class'=>'btn btn-primary mr-2', 'style'=>'display:none', 'onclick'=>'executar()'), 'Executar'); ?>
                  </div>
                <?php echo form_close(); ?>  
              </div>
            </div> 
          </div>
        </div>
      </div>
    </section>

    <script type="text/javascript">
      
      $("#swal-2").click(function () {
        swal("Good Job", "You clicked the button!", "success");
      }); 

      function executar(){
        swal("Good Job", "You clicked the button!", "success");

        // $('#swal-2').click();
      }
    </script>

