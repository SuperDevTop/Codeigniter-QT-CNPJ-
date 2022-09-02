
<!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <ul class="breadcrumb breadcrumb-style ">
            <li class="breadcrumb-item">
              <h4 class="page-title m-b-0">Advance Table</h4>
            </li>
            <li class="breadcrumb-item">
              <a href="index.html">
                <i data-feather="home"></i></a>
            </li>
            <li class="breadcrumb-item active">Table</li>
            <li class="breadcrumb-item active">Advance</li>
          </ul>
          <div class="section-body"> 
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4>TOTAL DE EMPRESAS = <?php echo $qtd->valor; ?></h4>
                    <div class="card-header-form">
                      <form>
                        <div class="input-group">
                          <input type="text" class="form-control" placeholder="Search">
                          <div class="input-group-btn">
                            <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                  
                  <div class="card-body p-0">
                    <div class="table-responsive"> 
                      <table class="table table-striped table-hover" id="save-stage" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Nome do Banco</th>
                            <th>Data de Última Atualização</th>
                            <th>Status</th> 
                          </tr>
                        </thead>
                        <tbody>                          
                          
                          <?php $contador = 0; ?>
                          <?php foreach ($monitor as $m): ?>
                          <tr>
                            <td><?php echo $contador ?></td>
                            <td><?php echo $m->banco_de_dados ?></td>
                            <td><?php echo $m->data_atualizacao ?></td>
                            <td><?php echo $m->status ?></td>
                            
                            <?php $contador++; ?>
                          </tr>                            
                          <?php endforeach ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div> 
          </div>
        </section> 
      </div>
