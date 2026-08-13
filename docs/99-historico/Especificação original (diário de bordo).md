# Projeto PMED 2.0

# Descrição resumida

O Cliente a ser atendido é um gestor de plano de saúde, que conta com conveniados e prestadores de serviço. Seu negócio gira em torno de administrar recursos para pagar as faturas dos procedimentos médicos realizados pelos conveniados em um dos prestadores de serviço cadastrados.  
Este projeto visa atualizar um programa já existente chamado PMED. Sua principal função é gerenciar o fluxo interno de pacotes que contém faturas de procedimentos médicos enviadas pelos prestadores de serviço ao plano de saúde deste cliente.

# Glossário de termos técnicos

PMED \- Programa de gestão de fluxo de pacotes de faturas  
OCS/PSA \- Organizações Civis de Saúde / Prestadores de Serviço Autônomos  
Pacote \- Unidade de controle, contém uma ou mais faturas de procedimentos enviadas pela OSC/PSA  
Protocolo \- Ponto de entrada e controle de todos os pacotes com faturas, constitui uma das equipes do fluxo  
Lisura \- Equipe responsável pela análise inicial do pacote, faz a verificação da integridade do valor cobrado na fatura e identifica possíveis Glosas  
SIRE \- Refere-se à equipe que opera o SIRE, sistema de autorização de pagamento de faturas do plano de saúde  
Glosa \- Refere-se à equipe que vai tratar das inconsistências (Glosas) identificadas pela equipe de Lisura.  
Arquivo \- Equipe que gerencia o arquivamento dos pacotes ao final de seu fluxo  
Recurso de Glosa \- Processo iniciado por uma OCS/PSA quando discorda do valor da glosa  
Estado Geral \- Indica como o fluxo do pacote está, inicializado com “Normal”  
Estado da Glosa \- Estado especial referente ao processo de Glosa, Inicializado com “Glosa não Identificada”  
Ofício de Glosa \- Documento Oficial pelo qual a OCS/PSA é notificada da existência da Glosa e do prazo para entrar com recurso

# Equipes participantes do fluxo do pacote

Protocolo, Lisura, SIRE, Glosa e Arquivo

# Outros atores

Administrador e Auditor  
As equipes e os atores devem ser tratadas como grupo de usuários, as permissões do sistema serão por grupo de usuário.

# Limite de crédito

O plano de saúde trabalha com um valor estimado que pode gastar mensalmente para pagar faturas, este valor é definido pelo limite de crédito no sistema do plano de saúde (SIRE), quando não houver crédito disponível para pagar a fatura o operador do SIRE deve mudar o Estado do pacote manualmente para “Aguardando limite de Crédito”

# Fluxo do trabalho das equipes

Cada equipe tem uma responsabilidade a ser tomada sobre o pacote, como uma linha de montagem, assim que termina sua ação a equipe encaminha o pacote para a próxima equipe, depois de tomar esta ação, não pode tomar nenhuma outra sobre o pacote, exceto visualizar seu histórico de movimentações ou ações explicitamente permitidas.

# Fluxo básico de um pacote

Protocolo \-\> Lisura \-\> SIRE \-\> Glosa \-\> Arquivo \-\> Arquivado  
Existem outras variações no fluxo que vão depender da ação da equipe sobre o pacote, mas serão tratados individualmente mais adiante.

# Estados

Algumas ações sobre o pacote dependem de ações externas ao trabalho da equipe, por isso, para informar à gerência o motivo pelo qual o pacote não está se movendo no fluxo implementamos os Estados

## Estado Geral

O Estado Geral do pacote podem ser

* Normal \- estado inicial  
* Aguardando Limite de Crédito \- Quando não há Limite suficiente para pagar a fatura  
* Arquivado \- Ciclo de vida encerrado

## Estados da Glosa

A Glosa é um processo que depende de iteração com a OCS/PSA, quando uma glosa é identificada a OCS/PSA é informada e o processo se inicia podendo gerar os seguintes estados:

* Glosa não identificada \- estado inicial  
* Glosa identificada \- quando a equipe de Lisura informa que identificou uma glosa  
* OCS/PSA notificada de existência de glosa \- Início do prazo de 30 dias para retirar Ofício de Glosa  
* OCS/PSA retirou Ofício de Glosa \- registra que a OCS/PSA compareceu e retirou o Ofício de Glosa  
* Aguardando recurso de Glosa \- Início do prazo de 30 dias para entrar com o recurso. Estado intermediário enquanto aguarda recurso, esse estado envolve novamente a equipe Protocolo. Neste ponto a Equipe Glosa não tem nenhuma ação disponível sobre o pacote além da Ação “Recurso não recebido”, a localização é Glosa, mas agora ele gera uma nova ação disponível para a equipe protocolo, a Ação “Recebimento de recurso de Glosa”. Esta ação será explicada mais adiante  
* Recurso recebido \- estado na qual informa que a OCS/PSA entrou com um recurso sobre o valor glosado  
* Recurso não recebido \- quando a OCS/PSA tem a opção de entrar com recurso mas não o faz ou faz fora do prazo

# Campos que compõem um Pacote

* Número do Pacote \- auto\_increment, PK  
* OCS/SPA \- Cadastrada pelo Administrador, dropdown com filtro de pesquisa, deve constar nas informações do pacote pois a OCS/PSA pode ser desativada ou excluída, mas deve constar sempre nas informações do pacote.  
* Localização Atual \- Equipe ou localização física onde se encontra o pacote  
* Localização Anterior \- Equipe ou localização física onde se encontrava o pacote antes da movimentação, inicializada com o valor “Sistema”  
* Tipo de Conta \- dropdown com as seguintes opções: Ambulatório, Home Care, Honorário, Internação, Laboratório, Oncologia, PA, Reabilitação, Remoção, TRS \- (Hemodiálise), Recurso de Glosa  
* Última ação \- registra a última ação executada em relação ao pacote  
* Data de Entrada no Protocolo \- tipo date, não pode ser data futura, somente hoje ou anterior  
* Valor da Fatura \- Valor a ser pago descrito na fatura  
* Glosa \- Valor da inconsistência encontrada pela equipe Lisura  
* Valor Pós Lisura \- resultante da subtração entre (Valor da Fatura) \- (Glosa)  
* Valor Pago \- Informado Pelo operador do SIRE após uma operação de Pagamento  
* Número da Fatura \- Número descrito na fatura  
* Tipo \- dropdown com as seguintes opções: Consulta, Exame, Internação, Óbito  
* Recurso de Glosa \- Valor referente ao recurso de glosa que será pago à OCS/PSA caso o recurso seja deferido  
* Arquivo \- campo descritivo que a equipe de Arquivo usa para gerenciar a localização física do pacote arquivado  
* Valor Pendente \- Valor da fatura que falta ser pago pelo operador do SIRE, é iniciado com o mesmo valor do campo Valor Pós lisura, é atualizado quando a equipe de Lisura insere um valor de glosa e posteriormente por registros de pagamentos pela equipe SIRE  
* Estado Geral \- Indica o estado do fluxo do pacote  
* Estado da Glosa \- Estado especial do processo de Glosa  
* Valor Recursado \- Valor que a OCS/PSA questionou sobre o valor glosado, inicializado com zero  
* Valor Deferido \- Valor que foi deferido após análise do recurso, inicializado com zero

# Histórico de movimentações

Todas e qualquer ação sobre o pacote deve gerar um histórico de movimentações contendo

* Data:   
* Ação:   
* Mensagem:   
* Observação:   
* Localização Pós Ação:   
* Estado Geral:   
* Estado da Glosa:   
* Usuário: 

- Este histórico poderá ser consultado por qualquer usuário logado.  
- O sistema deve fazer a gerência eficiente do Histórico para evitar consumo excessivo de disco

# Localização do pacote

Registra para fins de rastreamento o local (equipe) no qual o pacote se encontra fisicamente.

# Ação Mover (para todas as ações do tipo Mover)

Esta ação muda a localização do pacote e sinaliza o fluxo do pacote para a próxima localização(equipe)  
Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Movimento de pacote  
Mensagem: Movido de \<localização anterior\> para \<Localização atual\>  
Observação: \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: \<localização atual\>  
Estado Geral: \<Estado Geral atual do pacote\>  
Estado da Glosa: \<Estado da Glosa atual do pacote\>  
Usuário: \<usuário que realizou a ação\>

# Descrição do trabalho dos atores e equipes 

## Administrador

Gerencia as configurações do sistema  
CRUD de OCS/PSA  
CRUD de usuarios  
Pode editar as informações do pacote que foram preenchidas pelas equipes Protocolo e Lisura  
Pode realizar todas as ações sobre o pacote usando a mesma lógica da equipe na qual o pacote se encontra.  
 Possui menus Laterais exclusivos como Configurações, Usuários e Integrações

### Configurações do sistema 

O usuário Administrador poderá alterar coisas como Logotipo, Nome do Sistema, Descrição do Sistema, Favicon, e dados de usuários

### CRUD de OCS/PSA

\- Dados necessários para cadastro: Nome, Código Interno.  
\- Após a finalização do cadastro a OCS/PSA ganha um status Ativa, podendo ser tornada Inativa através de um switch button nas suas configurações.   
\- Editar OCS/PSA: Os campos Nome e Código Interno e Status (Ativa e inativa) podem ser alterados.   
\- Excluir: Exclui permanente a OCS/PSA

### CRUD de usuários 

\- As Equipes (grupo de usuários) disponíveis são: Administrador, Auditor, Protocolo, Lisura, SIRE, Glosa e Arquivo  
\- Cadastrar novo usuário com seguintes atributos: Nome, senha, email, Equipe (grupo de usuários).   
\- A validação do login será através de email e senha.

## Auditor

Pode acessar todas as views do sistema gerar relatórios e ver movimentações, mas não pode tomar nenhuma ação sobre o fluxo do pacote

## Protocolo

Recebe fisicamente os pacotes com as faturas  vindo da OCS/PSA  
Acessa o sistema e cria um novo pacote através de um botão que chama a ação inicial do fluxo “Criar Novo Pacote”  
Preenche o formulário com os dados iniciais e salvar, esta ação cria um novo pacote no sistema cujo número é sequencial e auto incremento. É a Primary Key do pacote.

### Ação Criar Novo Pacote

- Esta ação está disponível somente para a equipe Protocolo  
- A criação de um novo pacote abre um formulário que solicita as seguintes informações iniciais:  
  OCS/PSA \- dropdown com filtro de localização, Se a OCS/PSA estiver com status inativa deve aparecer com cor de fundo diferente  
  Valor da Fatura(R$)  
  Tipo \- dropdown com as seguintes opções: Consulta, Exame, Internação, Óbito  
  Número da Fatura  
  Data de Entrada no Protocolo \- Tipo date dd/mm/yyyy, com widget de calendário para selecionar a data e tratamento para impedir que uma data futura possa ser selecionada, só permite data de hoje ou anterior.

Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Criar novo Pacote  
Mensagem: Pacote incluído no sistema  
Observação: \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: Protocolo  
Estado Geral: Normal  
Estado da Glosa: Glosa não identificada  
Usuário: \<usuário que realizou a ação\>

### Edição

Somente enquanto a localização do pacote for Protocolo, os membros desta equipe podem editar todos os seus campos.

- Esta ação não move o pacote  
- Esta ação não muda o Estado Geral do Pacote  
- Esta ação não muda o Estado da Glosa  
- 

Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Edição  
Mensagem: \<campo editado\>: Anterior: \<valor antes da edição\> Novo:\<novo valor do campo\>  
Observação: \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: Protocolo  
Estado Geral: Normal  
Estado da Glosa: Glosa não identificada  
Usuário: \<usuário que realizou a ação\>

### Ação Mover

A ação do botão Mover para este grupo encaminha o pacote obrigatoriamente para a Equipe Lisura, esta ação muda a localização do pacote para Lisura e impossibilita que a equipe Protocolo tome qualquer ação sobre o pacote, exceto visualizar o histórico. Também gera log no histórico de movimentação do tipo Mover

## Lisura

Equipe recebe o pacote vindo de Protocolo e faz o trabalho de análise para averiguar inconsistências (glosas)

Caso verifique alguma glosa a equipe deve editar o pacote e inserir o valor referente à glosa.

### Edição

Enquanto a localização do pacote for Lisura os usuários desta equipe podem editá-lo e alterar os seguintes campos: Valor da Fatura, Tipo de Conta, Glosa

- Se o campo Glosa for diferente de zero muda o Estado da Glosa para “Glosa identificada”  
- O valor do campo Glosa não pode ser maior que o do campo Valor da Fatura(tratar)

Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Edição  
Mensagem: \<campo editado\>: Anterior: \<valor antes da edição\> Novo:\<novo valor do campo\>  
Observação: \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: Lisura  
Estado Geral: Normal  
Estado da Glosa: \<Glosa identificada ou Glosa não identificada\>  
Usuário: \<usuário que realizou a ação\>

### Ação Mover

A ação mover para esta equipe encaminha o pacote para a equipe SIRE, a partir disso, a equipe lisura não pode tomar nenhuma ação sobre o pacote, também gera log no histórico do tipo Mover

## SIRE

SIRE, apesar de dar nome à equipe dentro do PMED2, refere-se à equipe que opera uma ponte entre os 2 sistemas, SIRE e PMED2. O SIRE é um sistema corporativo onde o ciclo de vida da guia de consulta se inicia e onde deve terminar, não possui API externa, essa é uma das razões da existência do PMED2, acompanhar o processo de auditoria da guia fora do SIRE.  
A equipe recebe o pacote vindo de lisura e implanta os pagamentos no sistema do plano de saúde chamado SIRE

Trabalha sempre equilibrando o Limite de Crédito e o Valor Pendente

### Ação Aguardando Limite de crédito

Pode realizar a ação de mudar o Estado Geral do pacote para “Aguardando Limite de Crédito” enquanto:

-  Valor Pendente for maior que zero  
- A localização do pacote for: SIRE ou Glosa  
- Esta ação muda o Estado Geral do pacote para “Aguardando Limite de Crédito”  
- Esta ação não muda o Estado da Glosa  
- Esta ação não muda a localização do pacote

Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Aguardando Limite de Crédito  
Mensagem: Valor Pendente \<campo Valor Pendente\>  
Observação: \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: \<localização atual\>  
Estado Geral: Aguardando Limite de Crédito  
Estado da Glosa: \<Estado da Glosa atual do pacote\>  
Usuário: \<usuário que realizou a ação\>

### Ação Informar Pagamento

O operador deve verificar com frequência no programa SIRE se existe Limite de Crédito para pagar a fatura, se existir, ele deve usar a ação “Informar Pagamento”, na qual ele informa o montante que será pago da fatura, pode ser o Valor Pendente total (não maior que isso \- tratar) ou um valor parcial, para isso, essa ação abre um campo tipo hidden para que o usuário informe o “Valor do Pagamento”, este valor será descontado e atualiza o campo Valor Pendente, também deve somar e atualizar o campo Valor Pago.

A equipe SIRE pode realizar a ação de “Informar Pagamento” enquanto:

- Valor Pendente for maior que zero  
- A localização do pacote for: SIRE ou Glosa  
- Esta ação soma e atualiza o campo Valor Pago.  
- Esta ação subtrai e atualiza o campo Valor Pendente  
- Se o valor pendente for igual a zero esta ação não pode mais ser usada  
- Esta ação não muda a localização do pacote  
- Esta ação não muda o Estado Geral do pacote  
- Esta ação não muda o Estado da Glosa  
- Esta ação não muda a localização do pacote

Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Informar Pagamento  
Mensagem: Pagamento realizado no valor de \<Valor do Pagamento\>  
Observação: \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: \<localização atual\>  
Estado Geral: \<Estado Geral atual do pacote\>  
Estado da Glosa: \<Estado da Glosa atual do pacote\>  
Usuário: \<usuário que realizou a ação\>

### Ação Mover

O fluxo da ação mover para a localização SIRE se baseia no valor de alguns campos e na origem do pacote, ela gera também log do tipo Mover no histórico

- revisando a lógica de encaminhamento para a equipe sire:  
- \- Se o valor do campo Glosa for  igual a zero e o Valor Pendente for igual a zero \-\> Muda a localização atual para Arquivo e Altera o Estado Geral para Normal  
- \- Se o valor do campo Glosa for  igual a zero e o Valor Pendente for maior que zero \-\> Abre uma sweetalert com  a mensagem:  "Ação não permitida  
- Não é possível mover o pacote pois existe valor pendente de R$ \<valor pendnete\>. Informe os pagamentos antes de mover o pacote."  
- \- Se o campo Glosa for maior que zero, o campo Recurso de Glosa for igual a zero, a localização Anterior for Lisura e Valor Pendente for maior que zero \-\> Muda a localização atual para Glosa. Botões "Aguardando Limite de crédito" e "Informar pagamento" permanecem disponíveis para a equipe sire enquanto houver valor pendente, mesmo a localização sendo glosa  
- \- Se o campo Glosa for maior que zero, o campo Recurso de Glosa for maior que zero, a localização Anterior for Glosa e Valor Pendente igual a Zero \-\> Muda a localização atual para Arquivo e Altera o Estado Geral para Normal  
- \- Se o campo Glosa for maior que zero, o campo Recurso de Glosa for maior que zero, a localização Anterior for Glosa e Valor Pendente maior que Zero \-\> Abre uma modal informando:  "Ação não permitida  
- Não é possível mover o pacote pois existe valor pendente de R$ \<valor pendnete\>. Informe os pagamentos antes de mover o pacote."  
- \- Se o campo Glosa for maior que zero, o campo Recurso de Glosa for igual a zero, a localização Anterior for Glosa e Valor Pendente for igual a zero \-\> Muda a localização atual para Arquivo  Altera o Estado Geral para Normal  
  


## Glosa

Esta equipe possui algumas peculiaridades pois trata diretamente com o público externo, ela é acionada se uma glosa é identificada pela equipe de Lisura. Possui uma ordem bem definida das ações. Quando o pacote chega à Glosa, a primeira ação que a equipe deve tomar é notificar à OCS/PSA.

### Ação Notificação de Existência de Glosa

Esta ação registra que a OCS/PSA foi informada para comparecer e retirar o Ofício de Glosa, esta ação requer que o usuário informe em um campo descritivo como a OCS/PSA foi informada(Email, telefone, etc)

- Esta ação não muda a Localização do pacote  
- esta ação não muda o Estado Geral do pacote  
- Esta ação muda o Estado da Glosa para “OCS/PSA notificada de existência de Glosa”  
- Esta ação só pode ser executada uma vez por pacote e deve ser a primeira a ser executada, enquanto esta ação não for executada, nenhuma outra ação para a equipe glosa fica disponível,  
- Início do prazo de 30 dias para retirar Ofício de Glosa (pensar em uma forma de controlar e informar/controlar esse prazo no sistema)

Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Notificação de Existência de Glosa  
Mensagem: OCS/PSA notificada de existência de glosa  
Observação: \<conteúdo do campo descritivo informado pelo usuário\> \+ \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: \<localização atual\>  
Estado Geral: \<Estado Geral atual do pacote\>  
Estado da Glosa: OCS/PSA notificada de existência de glosa  
Usuário: \<usuário que realizou a ação\>

### Ação Retirada de Ofício de Glosa

Esta ação registra que a OCS/PSA retirou o Ofício de Glosa

- Esta ação não muda a Localização do pacote  
- esta ação não muda o Estado Geral do pacote  
- Esta ação muda o Estado da Glosa para “OCS/PSA retirou Ofício de Glosa”  
- Esta ação só pode ser executada uma vez por pacote e deve ser a segunda a ser executada, enquanto esta ação não for executada, nenhuma outra ação para a equipe glosa fica disponível,


  
Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Retirada de Ofício de Glosa  
Mensagem: OCS/PSA retirou Ofício de Glosa  
Observação: \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: \<localização atual\>  
Estado Geral: \<Estado Geral atual do pacote\>  
Estado da Glosa: OCS/PSA retirou Ofício de Glosa  
Usuário: \<usuário que realizou a ação\>

Ação automática(importante): Após gerar o Log esta ação dispara um gatilho que aciona automaticamente a Ação” Aguardando Recurso de glosa”

Ação Aguardando Recurso de Glosa  
Esta ação registra no sistema que o pacote está parado aguardando a ação da OCS/PSA de entrar (ou não) com o recurso de Glosa

- Esta ação não muda a Localização do pacote  
- Esta ação não muda o Estado Geral do pacote  
- Esta ação muda o Estado da Glosa para “Aguardando Recurso de Glosa”  
- Esta ação só pode ser executada uma vez por pacote e deve ser a terceira a ser executada, enquanto esta ação não for executada, nenhuma outra ação para a equipe glosa fica disponível  
- Início do prazo de 30 dias para Entrar com o recurso (pensar em uma forma de controlar e informar/controlar esse prazo no sistema)

Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Aguardando Recurso de Glosa  
Mensagem: Aguardando Recurso de Glosa  
Observação: \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: \<localização atual\>  
Estado Geral: \<Estado Geral atual do pacote\>  
Estado da Glosa: Aguardando Recurso de Glosa  
Usuário: \<usuário que realizou a ação\>

Esta ação tem um comportamento especial, pois quando o Estado da Glosa for “Aguardando Recurso de Glosa” três coisas devem ocorrer automaticamente:

- A Ação “Recebimento de recurso de Glosa” fica disponível para a equipe Protocolo, MESMO QUE A LOCALIZAÇÃO DO PACOTE SEJA GLOSA(importante \- tratar)  
- Surge para a equipe de glosa  a Ação “Recurso não recebido”, que interrompe a disponibilidade do pacote para o protocolo caso a OCS/PSA não entre com o recurso ou entre fora do prazo e dá encaminhamento ao pacote  
- Somente a ação “Recurso não recebido” fica disponível para a equipe de glosa


### Ação Recurso não recebido

Enquanto a OCS/PSA não entra com o recurso, ela fica disponível para a equipe de glosa se o Estado da Glosa for “Aguardando recurso de glosa”.

- Esta ação muda a Localização do pacote para Arquivo se o Valor Pendente for igual a zero   
- Esta ação muda a Localização do pacote para SIRE se o Valor Pendente for maior que zero  
- Esta ação não muda o Estado Geral do pacote  
- Esta ação muda o Estado da Glosa para “Recurso não recebido”  
- Esta ação só pode ser executada uma vez por pacote  
- Esta ação interrompe a disponibilidade para a equipe Protocolo com a mudança do Estado de Glosa

Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Recurso não recebido  
Mensagem: Recurso não recebido  
Observação: \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: Arquivo  
Estado Geral: \<Estado Geral atual do pacote\>  
Estado da Glosa: Recurso não recebido  
Usuário: \<usuário que realizou a ação\>

### Ação Recebimento de recurso de Glosa

Esta ação deve ser tomada pela equipe Protocolo, MESMO QUE A LOCALIZAÇÃO SEJA GLOSA (tratar), ela registra no sistema que a OCS/PSA entrou com o recurso de Glosa e dá prosseguimento no fluxo da Glosa.

- Esta ação deve ser tomada pela equipe Protocolo quando o Estado da Glosa for “Aguardando recurso de Glosa”  
- Esta ação não muda a Localização do pacote  
- Esta ação não muda o Estado Geral do pacote  
- Esta ação muda o Estado da Glosa para “Recurso recebido”  
- Esta ação só pode ser executada uma vez por pacote  
- Esta ação interrompe a disponibilidade para a equipe Glosa da Ação “Recurso não recebido” com a mudança do Estado de Glosa  
- Esta ação Habilita duas ações para a equipe Glosa, “Recurso Indeferido” e “Recurso deferido”

Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Recebimento de recurso de Glosa  
Mensagem: Recurso recebido  
Observação: \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: Glosa  
Estado Geral: \<Estado Geral atual do pacote\>  
Estado da Glosa: Recurso recebido  
Usuário: \<usuário que realizou a ação\>

### Ação Recurso indeferido

Esta ação registra que o recurso foi recebido, analisado porém foi indeferido

- Esta ação muda a Localização do pacote para Arquivo se o Valor Pendente for igual a zero  
- Esta ação muda a Localização do pacote para SIRE se o Valor Pendente for maior que zero  
- Esta ação não muda o Estado Geral do pacote  
- Esta ação muda o Estado da Glosa para “Recurso indeferido”  
- Esta ação só pode ser executada uma vez por pacote

Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Recurso indeferido  
Mensagem: Recurso indeferido  
Observação: \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: \<Arquivo ou SIRE\>  
Estado Geral: \<Estado Geral atual do pacote\>  
Estado da Glosa: Recurso indeferido  
Usuário: \<usuário que realizou a ação\>

### Ação Recurso deferido

Esta ação registra que o recurso foi aceito, total ou parcialmente, requer que usuário entre com duas informações Valor Recursado e Valor Deferido

- Esta ação abre dois campo hidden para que o usuário informe o Valor Recursado (não pode ser maior que o Valor do campo Glosa \- tratar) e Valor Deferido (não pode ser maior que o Valor Recursado \- tratar)  
- Esta ação muda a Localização do pacote para SIRE  
- Esta ação soma e atualiza o valor do campo Valor Pendente com o valor informado no campo Valor Deferido  
- Esta ação não muda o Estado Geral do pacote  
- Esta ação muda o Estado da Glosa para “Recurso deferido”  
- Esta ação só pode ser executada uma vez por pacote

Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Recurso deferido  
Mensagem: Recurso deferido  
Observação: \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: SIRE  
Estado Geral: \<Estado Geral atual do pacote\>  
Estado da Glosa: Recurso deferido  
Usuário: \<usuário que realizou a ação\>

## Arquivo

Esta equipe recebe pacotes vindos de SIRE ou Glosa e tem por finalidade finalizar o processo e encerrar o ciclo de vida do pacote. tem basicamente uma ação sobre o fluxo do pacote, Arquivar

### Ação Arquivar

Usuário informa o campo descritivo Arquivo e o pacote é movido para a localização Arquivado

- Enquanto a localização do pacote for Arquivo ou Arquivado a equipe consegue editar o campo Arquivo  
- Esta ação muda a Localização do pacote para Arquivado  
- Esta ação  muda o Estado Geral do pacote para Arquivado  
- Esta ação não muda o Estado da Glosa   
- Esta ação só pode ser executada uma vez por pacote  
  


Gera Log no histórico com:   
Data: \<Timestamp\>   
Ação: Arquivo  
Mensagem: Pacote arquivado  
Observação: \<Se houver algo preenchido neste campo\>  
Localização Pós Ação: Arquivado  
Estado Geral: Arquivado  
Estado da Glosa:\<Estado da Glosa atual\>  
Usuário: \<usuário que realizou a ação\>

# Menus Laterais

Dispõe sobre os menus Laterais e quais equipes podem vê-lo e acessá-lo

## Dashboard

Acessa a view Dashboard, visível a todos os usuários logados, é a Home page

## Configurações

Acessa a view de configurações do sistema, somente visível ao grupo Administrador

##  Usuários

Acessa a view de usuários do sistema, somente visível ao grupo Administrador

## Pacotes

Acessa a view onde são listados os pacotes, visível a todos os usuários logados

## Relatórios

Dá acesso à view com os relatórios pré-configurados do sistema, visível a todos os usuários logados

## Pesquisa

Dá acesso à view com funções de pesquisa avançada, visível a todos os usuários logados

## Gráficos

Dá acesso à view com gráficos pré-configurados do sistema, visível a todos os usuários logados

# Views

## Dashboard

Descrição: Apresenta um dashboard que tem no topo cards com a quantidade de pacotes em cada localização, ao centro um gráfico circular com a distribuição dos pacotes por localização, exceto a localização Arquivado, outr gráfico de barras com os valores mais importantes como Valor Total dos pacotes em tramitação, Valor Total Pendente, Valor Total Glosado, etc  
Na parte inferior uma série de análises e insights relevantes para o negócio.

## Configurações

A definir

## Usuários

View padrão, com listagem de usuários e botões para chamar a CRUD

## Pacotes

É a view mais importante do sistema  contém as seguintes Abas (navbar) que filtram o pacote por Localização Atual:  
Protocolo, Lisura, SIRE, Glosa, Arquivo, Arquivados

Dentro de cada aba (navbar) existe uma datatable completa, com filtro Search, ordenação nas colunas e paginação, nela só serão exibidos os pacotes que correspondem à Localização atual igual à da Aba, possui as seguintes colunas:  
Nº do Pacote, Data Protocolo, Nº da Fatura, OCS/PSA, Valor da Fatura, Tipo, Ações (Ver e Editar(se possível para este usuário) Mover)

### Ação Mover em Lote

A primeira coluna da datatable é um checkbox para a ação Mover em Lote (botão na parte superior e inferior da tela), que executa a ação Mover em lote e de forma atômica para cada pacote selecionado (se houver algum pacote que, pela lógica, não puder ser movido, nenhum dos selecionados será movido \- exibir mensagem de erro e motivo). Ação visível para todas as equipes, exceto Arquivo.

### Ação Arquivar em Lote

A primeira coluna da datatable é um checkbox para a ação Arquivar em Lote (botão na parte superior e inferior da tela), que executa a ação Arquivar em lote e de forma atômica para cada pacote selecionado. Ação visível para a equipe Arquivo.

### Ação Mover Arquivo em Lote

A primeira coluna da datatable é um checkbox para a ação Mover Arquivo em Lote (botão na parte superior e inferior da tela), solicita a entrada da informação “Nova Localização de Arquivo”, altera o campo Arquivo de cada pacote selecionado de forma atômica para. Ação visível para a equipe Arquivo nas Abas Arquivo e Arquivado.

### Ação Mover da coluna Ação da datatable

Executa a ação Mover disponível para o pacote. Ação visível para todas as equipes, exceto Arquivo.

Ação Arquivar da coluna Ação da datatable  
Visível somente para a equipe Arquivo. Executa a ação Arquivar

### Ação Ver da coluna Ação da datatable

Ação visível para todas as equipes, exceto Arquivo. View acionada pela Ação “Ver” da datatable em cada pacote, apresenta de forma organizada em colunas as seguintes informações do pacote exatamente nesta ordem:  
Coluna 1: Nº do Pacote, OCS/PSA, Localização Atual, Localização Anterior, Última Ação  
Coluna 2: Data da Entrada no Protocolo, Valor da Fatura, Glosa, Valor Pós Lisura, Valor Pago  
Coluna 3: Número da Fatura, Tipo, Tipo de Conta, Recurso de Glosa, Valor Pendente  
Coluna 4: Arquivo, Estado Geral, Estado da Glosa, Valor Recursado, Valor Deferido

Possui na parte inferior os botões: Mover (se for possível na lógica atual do pacote), Editar (se for possível na lógica atual do pacote)  Voltar (tela anterior), Movimentações(View Histórico de movimentações), Criar Novo Pacote(Somente para a equipe Protocolo)

### View de Edição dos Pacotes(Ação Editar da coluna ação da datatable)

View simples de edição dos pacotes, segue a lógica já informada para cada equipe (consultar as lógicas das Ações de Editar). Ação visível para todas as equipes, exceto Arquivo.

### View de Histórico de Movimentações

View estilo timeline com os campos do Histórico de movimentações

## Relatórios

A definir

## Pesquisa

A definir

## Gráficos

A definir

## Pagamento

Vamos expandir o sistema com novas funcionalidades. A próxima funcionalidade é a de Mapas de pagamento.  
Explicação: As faturas já são informadas no sistema, elas compõem o pacote, porém, nem sempre são pagas completamente, recorrentemente são pagas parcialmente, de acordo com a quantidade de crédito disponível para isso, portanto, foi criada o Mapa de Pagamento, onde é informado o número do mapa, data de criação do mapa, fatura(buscar no sistema), o valor parcial da fatura a ser pago, o empenho (forma de pagamento governamental), data do empenho, nota fiscal e data da nota fiscal.

O resultado final é um cruzamento de dados entre mapas e faturas, onde:  
\- Uma fatura pode constar em vários mapas e a soma dos valor dos mapas tem que bater com o valor total da fatura  
\- Um mapa pode ser composto de vários pagamentos parciais de faturas

Ou seja, preciso ser capaz de consultar uma mapa e ver todas as faturas que constam nele assim como consultar uma fatura e ver todos os mapas dos quais ela faz parte.

Para essa tarefa precisamos criar mais um grupo de usuários na tabela de dados de usuários chamado “pagamento”, apenas esse grupo de usuários e os administradores poderão fazer CRUD dos Mapas.

Criar uma nova tabela para armazenar os mapas e seus campos já citados.

No menu lateral, precisamos criar um Menu nível 1 chamado Pagamento, e abaixo dele, dois Menus nível 2 (Mapas e Pesquisa de Mapas)  
No menu Mapas faremos o CRUD de mapas e no menu Pesquisa de Mapa faremos a listagem e pesquisa avançada por cada campo individualmente

Vamos começar criando os menus e as views de exemplo 

# Requisitos do sistema e ambiente

Servidor: Ubuntu Server 24.04 LTS self hosted.  
Laravel com template AdminLTE V3   
PHP8.3  
Php Artisan  
Banco de dados MariaDB: pmed2  
PHPMyAdmin para manipulação do banco de dados

Usuário admin: [admin@pmed2.com](mailto:admin@pmed2.com)

Protocolo

Email: protocolo@4rm.eb.mil.br  
Papel: protocolo

Lisura  
Email: lisura@4rm.eb.mil.br  
Papel: lisura

SIRE  
Email: sire@4rm.eb.mil.br  
Papel: sire

Glosa  
Email: glosa@4rm.eb.mil.br  
Papel: glosa

Arquivo  
Email: arquivo@4rm.eb.mil.br  
Papel: arquivo

o botão resetar senha troca a senha para brasil@123

## Zerar Pacotes no Banco de dados

mysql \-u pmed2user \-p pmed2

SET FOREIGN\_KEY\_CHECKS \= 0;  
TRUNCATE TABLE movimentacoes\_pacote;  
TRUNCATE TABLE movimentacoes;  
TRUNCATE TABLE glosas;  
TRUNCATE TABLE pacotes;  
SET FOREIGN\_KEY\_CHECKS \= 1;

#  Desenvolvimento

Para pegar schema da tabela  
Schema::getColumnListing('tipos\_pacote');  
ou   
App\\Models\\Pacote::where('localizacao\_atual', 'lisura')-\>get();

Para verificar todos os pacotes na localização 'lisura':  
App\\Models\\Pacote::where('localizacao\_atual', 'lisura')-\>get();

Para verificar um pacote específico (substitua ID pelo número do pacote):  
App\\Models\\Pacote::find(ID);

Para verificar apenas a localização de um pacote específico:  
App\\Models\\Pacote::where('id', ID)-\>value('localizacao\_atual');

mudar a localização de um pacote 

$pacote \= App\\Models\\Pacote::find(ID);  
$pacote-\>localizacao\_anterior \= 'origem\_desejada'; // Ex: 'lisura', 'sire', 'protocolo', 'glosa', 'arquivo'  
$pacote-\>localizacao\_atual \= 'destino\_desejado';   // Ex: 'lisura', 'sire', 'protocolo', 'glosa', 'arquivo'  
$pacote-\>save();

para verificar as alterações aplicadas 

App\\Models\\Pacote::find(ID);

Exemplo:  
$pacote \= App\\Models\\Pacote::find(30);  
$pacote-\>localizacao\_anterior \= 'glosa';  
$pacote-\>localizacao\_atual \= 'sire';  
$pacote-\>save();

Para registrar a movimentação:  
use Carbon\\Carbon;  
use Illuminate\\Support\\Facades\\Auth;

$movimentacao \= new App\\Models\\MovimentacaoPacote();  
$movimentacao-\>pacote\_id \= ID;  
$movimentacao-\>acao \= 'Correção Manual';  
$movimentacao-\>mensagem \= 'Correção manual de localização';  
$movimentacao-\>observacao \= 'Correção de inconsistência de localização';  
$movimentacao-\>localizacao\_pos\_acao \= 'destino\_desejado';  
$movimentacao-\>estado\_geral \= $pacote-\>estado\_geral;  
$movimentacao-\>estado\_glosa \= $pacote-\>estado\_glosa;  
$movimentacao-\>usuario\_id \= Auth::id();  
$movimentacao-\>save();

mudar valor pendnete  
$pacote \= App\\Models\\Pacote::find(30);  
$pacote-\>valor\_pendente \= 0.01;    
$pacote-\>save();

#  Apagar Banco e Recuperar com .sql  Apagar o banco

mysql \-u pmed2user \-p \-e "DROP DATABASE pmed2; CREATE DATABASE pmed2;"

Restaurar banco  
mysql \-u pmed2user \-p pmed2 \< caminho/para/seu/backup.sql

php artisan tinker

Quando o console do tinker abrir, execute um dos seguintes comandos:

mostra todas as tabelas do banco  
DB::select('SHOW TABLES');

apaga a tabela se ela existir  
DB::statement('DROP TABLE IF EXISTS notificacoes');

schema da tabela  
Schema::getColumnListing('tipos\_pacote');  
ou   
App\\Models\\Pacote::where('localizacao\_atual', 'lisura')-\>get();

Para verificar todos os pacotes na localização 'lisura':  
App\\Models\\Pacote::where('localizacao\_atual', 'lisura')-\>get();

Para verificar um pacote específico (substitua ID pelo número do pacote):  
App\\Models\\Pacote::find(ID);

Para verificar apenas a localização de um pacote específico:  
App\\Models\\Pacote::where('id', ID)-\>value('localizacao\_atual');

mudar a localização de um pacote 

$pacote \= App\\Models\\Pacote::find(ID);  
$pacote-\>localizacao\_anterior \= 'origem\_desejada'; // Ex: 'lisura', 'sire', 'protocolo', 'glosa', 'arquivo'  
$pacote-\>localizacao\_atual \= 'destino\_desejado';   // Ex: 'lisura', 'sire', 'protocolo', 'glosa', 'arquivo'  
$pacote-\>save();

para verificar as alterações aplicadas 

App\\Models\\Pacote::find(ID);

Exemplo:  
$pacote \= App\\Models\\Pacote::find(30);  
$pacote-\>localizacao\_anterior \= 'glosa';  
$pacote-\>localizacao\_atual \= 'sire';  
$pacote-\>save();

Para registrar a movimentação:  
use Carbon\\Carbon;  
use Illuminate\\Support\\Facades\\Auth;

$movimentacao \= new App\\Models\\MovimentacaoPacote();  
$movimentacao-\>pacote\_id \= ID;  
$movimentacao-\>acao \= 'Correção Manual';  
$movimentacao-\>mensagem \= 'Correção manual de localização';  
$movimentacao-\>observacao \= 'Correção de inconsistência de localização';  
$movimentacao-\>localizacao\_pos\_acao \= 'destino\_desejado';  
$movimentacao-\>estado\_geral \= $pacote-\>estado\_geral;  
$movimentacao-\>estado\_glosa \= $pacote-\>estado\_glosa;  
$movimentacao-\>usuario\_id \= Auth::id();  
$movimentacao-\>save();

mudar valor pendnete  
$pacote \= App\\Models\\Pacote::find(30);  
$pacote-\>valor\_pendente \= 0.01;    
$pacote-\>save();

SELECT   
    TABLE\_NAME,   
    COLUMN\_NAME,   
    COLUMN\_TYPE,   
    IS\_NULLABLE,   
    COLUMN\_DEFAULT   
FROM   
    INFORMATION\_SCHEMA.COLUMNS  
WHERE   
    TABLE\_SCHEMA \= 'pmed2'  
ORDER BY   
    TABLE\_NAME, ORDINAL\_POSITION;

## Pacotes Anulados

\-- Ver pacote anulado (valores zerados)  
SELECT id, numero\_fatura, valor\_fatura, valor\_pago, anulado, localizacao\_atual FROM pacotes WHERE id \= 931;

\-- Ver auditoria (valores originais preservados)  
SELECT pacote\_id, valor\_fatura\_original, valor\_pago\_original, motivo\_anulacao, data\_anulacao FROM pacotes\_anulados\_audit WHERE pacote\_id \= 931;

—----------------------------------------------------------------------------------------------------------------------------------------------------------------------

# Usando claude code

instalei graphify  
instalei github cli

tokens de leitura para repositório privado  
1\. Gerar o token (classic, não fine-grained — GHCR funciona melhor com esse tipo):

Acesse: https://github.com/settings/tokens/new  
Note: pmed2-ghcr-pull-homolog  
Expiration: recomendo 90 dias (evita token esquecido rodando pra sempre; registro isso no guia de decisões como pendência de rotação)  
Scopes: marque só read:packages (nenhum outro necessário)  
Gere e copie o token na hora — ele só aparece uma vez  
2\. Configurar os secrets — rode isso você mesmo no seu terminal (não cole o token pra mim, assim ele não fica no histórico da conversa):

gh secret set PMED2\_HOM\_GHCR\_USER \--repo xlipesousa/pmed2 \--body "xlipesousa"  
gh secret set PMED2\_HOM\_GHCR\_TOKEN \--repo xlipesousa/pmed2

O segundo comando, sem \--body, vai pedir pra você colar o token (ou apertar Ctrl+D depois de colar) — assim ele nunca aparece na tela de histórico do shell.

