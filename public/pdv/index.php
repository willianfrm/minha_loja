<?php
session_start();

//bloqueia acesso ao PDV se usuário não for opera_pdv
if(!$_SESSION['opera_pdv']){
	echo "<script>alert('Usuário sem permissão para operar PDV.'); window.location.href='../dashboard.php';</script>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>PDV - Caixa</title>
  <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body, html { height: 100%; margin: 0; }
    #pdv-container { display: flex; flex-direction: column; height: 100%; }
    #pdv-header { background: #343a40; color: #fff; padding: 10px; }
    #pdv-main { flex: 1; overflow-y: auto; padding: 10px; }
    #pdv-footer { background: #f8f9fa; padding: 10px; display: flex; gap: 10px; }
    #pdv-footer input { font-size: 1.5rem; }
    table { width: 100%; }
  </style>
</head>
<body>
<div id="pdv-container">
  <div id="pdv-header">
    <h4>Minha Loja - PDV</h4>
    <span>Operador: <?= htmlspecialchars($_SESSION['nome'] ?? '---') ?></span>
    <span class="float-end" id="clock"></span>
  </div>

  <div id="pdv-main">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th><th>Código</th><th>Descrição</th><th>Qtd</th><th>Unit</th><th>Desc</th><th>Subtotal</th>
        </tr>
      </thead>
      <tbody id="itens-venda">
        <!-- itens da venda aqui -->
      </tbody>
    </table>
  </div>

  <div id="pdv-footer">
    <input type="number" step="0.001" id="quantidade" value="1" class="form-control" style="width:150px">
    <input type="text" id="codigo" class="form-control" placeholder="Código/EAN" autofocus>
    <h3 class="ms-auto" style="width:450px">Total: R$ <span id="total">0,00</span></h3>
  </div>
</div>

<!-- Modal genérico para opções -->
<div class="modal fade" id="menuModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Menu de Opções (F1)</h5></div>
      <div class="modal-body">
        <ul>
          <li>F2 - Consulta Produto por Nome</li>
          <li>F3 - Consulta Preço</li>
          <li>F4 - Cancela Item</li>
          <li>F5 - Cancela Venda</li>
          <li>F6 - Campo Quantidade (Teclas alternativas: * , x )</li>
          <li>F7 - Aplicar Desconto</li>
          <li>F8 - Finalizar Venda</li>
        </ul>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button></div>
    </div>
  </div>
</div>

<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
let menuAberto = false;
const menuModalEl = document.getElementById('menuModal');
const menuModal = new bootstrap.Modal(menuModalEl);

// Atalhos principais
document.addEventListener('keydown', function(e) {
  switch(e.key) {
    case "F1":
      e.preventDefault();
      if (menuAberto) {
        menuModal.hide();
        menuAberto = false;
      } else {
        menuModal.show();
        menuAberto = true;
      }
      break;

    case "F6":
    case "*":
    case "x":
    case "X":
      e.preventDefault();
      const qtdInput = document.getElementById('quantidade');
      qtdInput.focus();
      qtdInput.value = "";
      break;

    case "F2": e.preventDefault(); consultaProdutoNome(); break;
    case "F3": e.preventDefault(); consultaPreco(); break;
    case "F4": e.preventDefault(); cancelaItem(); break;
    case "F5": e.preventDefault(); cancelaVenda(); break;
    case "F7": e.preventDefault(); aplicarDesconto(); break;
    case "F8": e.preventDefault(); finalizarVenda(); break;
  }
});

// Fecha menu e volta foco
menuModalEl.addEventListener('hidden.bs.modal', function () {
  document.getElementById('codigo').focus();
  menuAberto = false;
});

// Enter em quantidade
document.getElementById('quantidade').addEventListener('keydown', function(e) {
  if (e.key === "Enter") {
    let qtd = parseFloat(this.value);
    if (isNaN(qtd) || qtd <= 0) this.value = 1;
    document.getElementById('codigo').focus();
  }
});

// Enter em código → adiciona produto
document.getElementById('codigo').addEventListener('keydown', function(e) {
  if (e.key === "Enter") {
    e.preventDefault();
    let codigo = this.value.trim();
    let qtd = parseFloat(document.getElementById('quantidade').value);
    if (isNaN(qtd) || qtd <= 0) qtd = 1;

    if (codigo !== "") {
      adicionarProduto(codigo, qtd);
      this.value = "";
    }
  }
});

// Relógio
setInterval(() => {
  document.getElementById('clock').textContent = new Date().toLocaleTimeString();
}, 1000);

// Carregar itens da sessão ao abrir página
function carregarItensSessao() {
  fetch("status_itens.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('itens-venda');
      tbody.innerHTML = "";
      data.itens.forEach((item, i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${i+1}</td>
          <td>${item.codigo_produto}</td>
          <td>${item.descricao}</td>
          <td>${item.quantidade}</td>
          <td>R$ ${parseFloat(item.valor_unitario).toFixed(2)}</td>
          <td>R$ ${parseFloat(item.desconto).toFixed(2)}</td>
          <td>R$ ${parseFloat(item.subtotal).toFixed(2)}</td>
        `;
        tbody.appendChild(tr);
      });
      document.getElementById('total').textContent = parseFloat(data.total).toFixed(2);
    })
    .catch(err => {
      console.error("Erro ao carregar itens da sessão:", err);
    });
}
// Executa ao carregar página
window.addEventListener("DOMContentLoaded", carregarItensSessao);


// Adicionar produto
function adicionarProduto(codigo, qtd) {
  fetch("adicionar_item.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "codigo=" + encodeURIComponent(codigo) + "&quantidade=" + encodeURIComponent(qtd)
  })
  .then(res => res.json())
  .then(data => {
    if (data.erro) { minhaMensagem(data.erro, "vermelho"); return; }

    const tbody = document.getElementById('itens-venda');
    tbody.innerHTML = "";
    data.itens.forEach((item, i) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${i+1}</td>
        <td>${item.codigo_produto}</td>
        <td>${item.descricao}</td>
        <td>${item.quantidade}</td>
        <td>R$ ${parseFloat(item.valor_unitario).toFixed(2)}</td>
        <td>R$ ${parseFloat(item.desconto).toFixed(2)}</td>
        <td>R$ ${parseFloat(item.subtotal).toFixed(2)}</td>
      `;
      tbody.appendChild(tr);
    });

    document.getElementById('total').textContent = parseFloat(data.total).toFixed(2);
    document.getElementById('quantidade').value = 1;
    document.getElementById('codigo').focus();
  })
  .catch(err => {
    console.error("Erro ao adicionar produto:", err);
    minhaMensagem("Erro ao adicionar produto.", "vermelho");
  });
}

// Fechar menu
function fecharMenuSeAberto() {
  if (menuAberto) {
    menuModal.hide();
    menuAberto = false;
    document.getElementById('codigo').focus();
  }
}

//Função consulta produto por nome
function consultaProdutoNome() {
  fecharMenuSeAberto();

  const modalHtml = `
    <div class="modal fade" id="consultaNomeModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Consulta Produto por Nome</h5></div>
          <div class="modal-body">
            <input type="text" id="nomeBusca" class="form-control" placeholder="Digite o nome do produto">
            <ul id="listaProdutos" class="list-group mt-2"></ul>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
          </div>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML("beforeend", modalHtml);

  const consultaModalEl = document.getElementById('consultaNomeModal');
  const consultaModal = new bootstrap.Modal(consultaModalEl);
  
  consultaModalEl.addEventListener('shown.bs.modal', () => {
    document.getElementById("nomeBusca").focus();
  });
  
  consultaModal.show();

  consultaModalEl.addEventListener("hidden.bs.modal", () => {
    consultaModalEl.remove();
    document.getElementById('codigo').focus();
  });

  const input = document.getElementById("nomeBusca");
  const lista = document.getElementById("listaProdutos");
  let selectedIndex = 0;

  input.addEventListener("input", () => {
    fetch("consulta_nome.php?nome=" + encodeURIComponent(input.value))
      .then(res => res.json())
      .then(produtos => {
        lista.innerHTML = "";
        produtos.forEach((p, i) => {
          const li = document.createElement("li");
          li.className = "list-group-item" + (i === 0 ? " active" : "");
          li.textContent = p.nome;
          li.dataset.codigo = p.codigo;
          lista.appendChild(li);
        });
        selectedIndex = 0;
      });
  });

  consultaModalEl.addEventListener("keydown", e => {
    const itens = lista.querySelectorAll("li");
    if (!itens.length) return;

    if (e.key === "ArrowDown") {
      e.preventDefault();
      selectedIndex = (selectedIndex + 1) % itens.length;
    }
    if (e.key === "ArrowUp") {
      e.preventDefault();
      selectedIndex = (selectedIndex - 1 + itens.length) % itens.length;
    }
    itens.forEach((li, i) => li.classList.toggle("active", i === selectedIndex));

    if (e.key === "Enter") {
      e.preventDefault();
      const codigo = itens[selectedIndex].dataset.codigo;
      consultaModal.hide();
      consultaModalEl.remove();
      document.getElementById("codigo").value = codigo;
      document.getElementById("codigo").focus();
    }
  });
}

// Função consultar preço
function consultaPreco() {
  fecharMenuSeAberto();

  const codigo = prompt("Digite o código ou EAN do produto para consultar:");
  if (!codigo) return;

  fetch("consulta_preco.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "codigo=" + encodeURIComponent(codigo)
  })
  .then(res => res.json())
  .then(data => {
    if (data.erro) { minhaMensagem(data.erro, "vermelho"); return; }

    let msg = `Produto: ${data.descricao}<br>Preço: R$ ${parseFloat(data.preco).toFixed(2)}`;
    if (data.oferta) msg += " (OFERTA)";
    minhaMensagem(msg, "azul");
  })
  .catch(err => {
    console.error("Erro ao consultar preço:", err);
    minhaMensagem("Erro ao consultar preço.", "vermelho");
  });
}

// Aplicar desconto
function aplicarDesconto() {
  fecharMenuSeAberto();

  const isAdminPDV = <?= $_SESSION['admin_pdv'] ? 'true' : 'false' ?>;
  if (!isAdminPDV) {
    const modalHtml = `
      <div class="modal fade" id="adminLoginModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Autorização de Admin</h5></div>
            <div class="modal-body">
              <input type="text" id="adminLogin" class="form-control mb-2" placeholder="Login">
              <input type="password" id="adminSenha" class="form-control mb-2" placeholder="Senha">
              <div id="adminErro" class="text-danger"></div>
            </div>
          </div>
        </div>
      </div>
    `;
    document.body.insertAdjacentHTML("beforeend", modalHtml);

    const modalEl = document.getElementById('adminLoginModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // Foco inicial
    modalEl.addEventListener("shown.bs.modal", () => {
      document.getElementById("adminLogin").focus();
    });

    // Ao fechar modal → volta foco para código
    modalEl.addEventListener("hidden.bs.modal", () => {
      modalEl.remove();
      document.getElementById("codigo").focus();
    });

    // Captura Enter dentro dos campos
    modalEl.addEventListener("keydown", e => {
      if (e.key === "Enter") {
        e.preventDefault();
        const login = document.getElementById("adminLogin").value.trim();
        const senha = document.getElementById("adminSenha").value.trim();

        fetch("autorizar_admin.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "login=" + encodeURIComponent(login) + "&senha=" + encodeURIComponent(senha)
        })
        .then(res => res.json())
        .then(data => {
          if (data.sucesso) {
            modal.hide();
            modalEl.remove();
            executarDesconto();
            // Após desconto → volta foco para código
            document.getElementById("codigo").focus();
          } else {
            document.getElementById("adminErro").textContent = data.erro;
            document.getElementById("adminSenha").focus();
          }
        })
        .catch(err => {
          console.error("Erro ao autorizar admin:", err);
          document.getElementById("adminErro").textContent = "Erro no servidor.";
        });
      }
    });

    return;
  }

  executarDesconto();
}


// Função real de desconto
function executarDesconto() {
  const itemNum = prompt("Digite o número do item para aplicar desconto:");
  if (!itemNum) return;
  const index = parseInt(itemNum) - 1;

  const desconto = parseFloat(prompt("Digite o valor do desconto por unidade:").replace(",", "."));
  if (isNaN(desconto) || desconto <= 0) {
    minhaMensagem("Desconto inválido.", "vermelho");
    return;
  }

  fetch("aplicar_desconto.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "index=" + encodeURIComponent(index) + "&desconto=" + encodeURIComponent(desconto)
  })
  .then(res => res.json())
  .then(data => {
    if (data.erro) { minhaMensagem(data.erro, "vermelho"); return; }

    const tbody = document.getElementById('itens-venda');
    tbody.innerHTML = "";
    data.itens.forEach((item, i) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${i+1}</td>
        <td>${item.codigo_produto}</td>
        <td>${item.descricao}</td>
        <td>${item.quantidade}</td>
        <td>R$ ${parseFloat(item.valor_unitario).toFixed(2)}</td>
        <td>R$ ${parseFloat(item.desconto).toFixed(2)}</td>
        <td>R$ ${parseFloat(item.subtotal).toFixed(2)}</td>
      `;
      tbody.appendChild(tr);
    });

    document.getElementById('total').textContent = parseFloat(data.total).toFixed(2);
  })
  .catch(err => {
    console.error("Erro ao aplicar desconto:", err);
    minhaMensagem("Erro ao aplicar desconto.", "vermelho");
  });
}

// Cancelar item
function cancelaItem() {
  fecharMenuSeAberto();

  const itemNum = prompt("Digite o número do item que deseja cancelar:");
  if (!itemNum) return;
  const index = parseInt(itemNum) - 1;

  fetch("cancelar_item.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "index=" + encodeURIComponent(index)
  })
  .then(res => res.json())
  .then(data => {
    if (data.erro) { minhaMensagem(data.erro, "vermelho"); return; }

    const tbody = document.getElementById('itens-venda');
    tbody.innerHTML = "";
    data.itens.forEach((item, i) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${i+1}</td>
        <td>${item.codigo_produto}</td>
        <td>${item.descricao}</td>
        <td>${item.quantidade}</td>
        <td>R$ ${parseFloat(item.valor_unitario).toFixed(2)}</td>
        <td>R$ ${parseFloat(item.desconto).toFixed(2)}</td>
        <td>R$ ${parseFloat(item.subtotal).toFixed(2)}</td>
      `;
      tbody.appendChild(tr);
    });

    document.getElementById('total').textContent = parseFloat(data.total).toFixed(2);
  })
  .catch(err => {
    console.error("Erro ao cancelar item:", err);
    minhaMensagem("Erro ao cancelar item.", "vermelho");
  });
}

// Cancelar venda
function cancelaVenda() {
  fecharMenuSeAberto();

  if (!confirm("Tem certeza que deseja cancelar a venda inteira?")) return;

  fetch("cancelar_venda.php")
    .then(res => res.json())
    .then(() => {
      document.getElementById('itens-venda').innerHTML = "";
      document.getElementById('total').textContent = "0,00";
      document.getElementById('quantidade').value = 1;
      document.getElementById('codigo').focus();
      minhaMensagem("Venda cancelada com sucesso.", "amarelo");
    });
}

// Finalizar venda
function finalizarVenda() {
  fecharMenuSeAberto();
  let totalAtual = parseFloat(document.getElementById('total').textContent.replace(",", "."));
  if (isNaN(totalAtual) || totalAtual <= 0) {
    minhaMensagem("Não há itens na venda para finalizar.", "amarelo");
    return;
  }
  abrirModalPagamento(totalAtual);
}

// Concluir finalização
function concluirFinalizacao() {
  fetch("finalizar_venda.php")
    .then(res => res.json())
    .then(data => {
      if (data.erro) { minhaMensagem(data.erro, "vermelho"); return; }
      if (data.sucesso) {
        minhaMensagem("Venda finalizada com sucesso! Documento: " + data.documento, "verde");
        document.getElementById('itens-venda').innerHTML = "";
        document.getElementById('total').textContent = "0,00";
        document.getElementById('quantidade').value = 1;
        document.getElementById('codigo').focus();
      } else {
        minhaMensagem("Erro ao finalizar venda.", "vermelho");
      }
    })
    .catch(err => {
      console.error("Erro ao finalizar venda:", err);
      minhaMensagem("Erro ao finalizar venda.");
    });
}

// Modal de pagamento
// Modal de pagamento
function abrirModalPagamento() {
  // Consulta estado atual da venda no back-end
  fetch("status_venda.php")
    .then(res => res.json())
    .then(data => {
      const saldoRestante = data.saldoRestante;
      const saldoPago = data.saldoPago;

      // Primeiro busca métodos de pagamento ativos
      fetch("listar_metodos.php")
        .then(res => res.json())
        .then(metodos => {
          let listaHtml = "";
          metodos.forEach((m, i) => {
            listaHtml += `<li class="list-group-item ${i === 0 ? "active" : ""}" data-metodo="${m.codigo}">${m.descricao}</li>`;
          });

          const modalHtml = `
            <div class="modal fade" id="pagamentoModal" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header"><h5 class="modal-title">Finalizar Venda</h5></div>
                  <div class="modal-body">
                    <p id="saldoRestante">Saldo restante: R$ ${saldoRestante.toFixed(2)}</p>
                    <p id="saldoPago">Saldo pago: R$ ${saldoPago.toFixed(2)}</p>
                    <ul id="listaMetodos" class="list-group">
                      ${listaHtml}
                    </ul>
                    <div id="valorBox" class="mt-3" style="display:none;">
                      <label>Valor:</label>
                      <input type="number" step="0.01" id="valorPagamento" class="form-control">
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  </div>
                </div>
              </div>
            </div>
          `;
          document.body.insertAdjacentHTML("beforeend", modalHtml);

          const pagamentoModalEl = document.getElementById('pagamentoModal');
          const pagamentoModal = new bootstrap.Modal(pagamentoModalEl);
          pagamentoModal.show();

          pagamentoModalEl.addEventListener("hidden.bs.modal", () => {
            pagamentoModalEl.remove();
          });

          let selectedIndex = 0;
          const itens = document.querySelectorAll("#listaMetodos li");

          function atualizarSelecao() {
            itens.forEach((li, i) => {
              li.classList.toggle("active", i === selectedIndex);
            });
          }

          pagamentoModalEl.addEventListener("keydown", function(e) {
            if (document.getElementById("valorBox").style.display === "none") {
              // Navegação nos métodos
              if (e.key === "ArrowDown") {
                e.preventDefault();
                selectedIndex = (selectedIndex + 1) % itens.length;
                atualizarSelecao();
              }
              if (e.key === "ArrowUp") {
                e.preventDefault();
                selectedIndex = (selectedIndex - 1 + itens.length) % itens.length;
                atualizarSelecao();
              }
              if (e.key === "Enter") {
                e.preventDefault();
                document.getElementById("valorBox").style.display = "block";
                document.getElementById("valorPagamento").focus();
              }
            } else {
              // Digitação do valor
              if (e.key === "Enter") {
                e.preventDefault();
                let metodoLi = itens[selectedIndex];
                let metodoCodigo = metodoLi.getAttribute("data-metodo");
                let valor = parseFloat(document.getElementById('valorPagamento').value.replace(",", "."));

                if (isNaN(valor) || valor <= 0) {
                  document.getElementById("valorPagamento").focus();
                  return;
                }

                // Chama registrar_pagamento.php
                fetch("registrar_pagamento.php", {
                  method: "POST",
                  headers: { "Content-Type": "application/x-www-form-urlencoded" },
                  body: "codigo_metodo=" + encodeURIComponent(metodoCodigo) +
                        "&valor=" + encodeURIComponent(valor)
                })
                .then(res => res.json())
                .then(data => {
                  if (data.erro) {
                    minhaMensagem(data.erro, "vermelho");
                    document.getElementById("valorPagamento").focus();
                    return;
                  }

                  // Atualiza saldo na tela
                  document.getElementById("saldoRestante").textContent =
                    "Saldo restante: R$ " + data.saldoRestante.toFixed(2);
                  document.getElementById("saldoPago").textContent =
                    "Saldo pago: R$ " + data.saldoPago.toFixed(2);

                  if (data.troco && data.troco > 0) {
                    minhaMensagem("Troco: R$ " + data.troco.toFixed(2), "azul");
                  }

                  if (data.saldoRestante > 0) {
                    // Ainda falta pagar
                    document.getElementById("valorPagamento").value = "";
                    document.getElementById("valorBox").style.display = "none";
                    atualizarSelecao();
                    document.querySelector("#pagamentoModal .btn-secondary").focus();
                  } else {
                    // Saldo zerado → finaliza venda
                    pagamentoModal.hide();
                    pagamentoModalEl.remove();
                    concluirFinalizacao();
                  }
                })
                .catch(err => {
                  console.error("Erro ao registrar pagamento:", err);
                  minhaMensagem("Erro ao registrar pagamento.", "vermelho");
                });
              }
            }
          });
        })
        .catch(err => {
          console.error("Erro ao listar métodos:", err);
          minhaMensagem("Erro ao listar métodos de pagamento.", "vermelho");
        });
    })
    .catch(err => {
      console.error("Erro ao abrir modal de pagamento:", err);
      minhaMensagem("Erro ao abrir modal de pagamento.", "vermelho");
    });
}

// Função para exibir mensagens bloqueantes com cores
function minhaMensagem(msg, cor = "preto") {
  // Remove qualquer mensagem anterior
  const oldOverlay = document.getElementById("pdvOverlay");
  if (oldOverlay) oldOverlay.remove();

  // Definição de estilos por cor
  let bgColor = "#343a40"; // preto padrão
  let textColor = "#fff";

  switch (cor.toLowerCase()) {
    case "verde":
      bgColor = "#28a745";
      textColor = "#fff";
      break;
    case "vermelho":
      bgColor = "#dc3545";
      textColor = "#fff";
      break;
    case "azul":
      bgColor = "#007bff";
      textColor = "#fff";
      break;
    case "amarelo":
      bgColor = "#ffc107";
      textColor = "#000"; // texto preto para contraste
      break;
    default:
      bgColor = "#343a40"; // preto
      textColor = "#fff";
  }

  const overlayHtml = `
    <div id="pdvOverlay" style="
      position: fixed; top:0; left:0; width:100%; height:100%;
      background: rgba(0,0,0,0.5); z-index:3000;
      display:flex; align-items:center; justify-content:center;">
      <div id="pdvToast" style="
        background:${bgColor}; color:${textColor}; padding:20px 40px;
        border-radius:8px; font-size:1.3rem; text-align:center;
        max-width:80%; box-shadow:0 0 10px #000;">
        ${msg}<br><small>(Pressione Enter para fechar)</small>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML("beforeend", overlayHtml);

  // Bloqueia interação até pressionar Enter
  function fecharMensagem(e) {
    if (e.key === "Enter" || e.key === "Escape") {
      const overlay = document.getElementById("pdvOverlay");
      if (overlay) overlay.remove();
      document.removeEventListener("keydown", fecharMensagem);
      // Volta foco para campo código
      document.getElementById("codigo").focus();
    }
  }
  document.addEventListener("keydown", fecharMensagem);
}

</script>
</body>
</html>
