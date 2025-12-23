<template>
  <div class="dashboard-kpi">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ titulo }}</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h4>{{ valor }}</h4>
          <span :class="'badge badge-' + tipoBadge">{{ variacao }}</span>
        </div>
        <div class="progress">
          <div 
            class="progress-bar" 
            :class="'bg-' + tipo" 
            role="progressbar" 
            :style="{ width: percentual + '%' }" 
            :aria-valuenow="percentual" 
            aria-valuemin="0" 
            aria-valuemax="100"
          ></div>
        </div>
        <small class="text-muted">{{ descricao }}</small>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    titulo: {
      type: String,
      required: true
    },
    valor: {
      type: String,
      required: true
    },
    variacao: {
      type: String,
      default: ''
    },
    percentual: {
      type: Number,
      default: 50
    },
    tipo: {
      type: String,
      default: 'info'
    },
    descricao: {
      type: String,
      default: ''
    }
  },
  computed: {
    tipoBadge() {
      if (this.variacao.includes('+')) {
        return 'success';
      } else if (this.variacao.includes('-')) {
        return 'danger';
      }
      return 'info';
    }
  }
}
</script>

<style scoped>
.dashboard-kpi .card {
  height: 100%;
}
.dashboard-kpi h4 {
  margin-bottom: 0;
  font-weight: bold;
}
</style>