<ul class="nav nav-pills flex-column">

  @can('is-admin')
    <x-sidebar-nav-link route="dashboard" icon="columns-gap">
      Dashboard
    </x-sidebar-nav-link>
  @endcan

  <x-sidebar-nav-link route="" icon="briefcase">
    Estágios
  </x-sidebar-nav-link>

  @can('is-admin')
    <li class="nav-item">
      <hr>
      <p class="text-muted small fw-bold text-uppercase mb-1">Configurações</p>
    </li>

    <x-sidebar-nav-link route="" icon="people">
      Gerenciar Usuários
    </x-sidebar-nav-link>

    <x-sidebar-nav-link route="" icon="book">
      Gerenciar Cursos
    </x-sidebar-nav-link>

    <x-sidebar-nav-link route="" icon="tags">
      Gerenciar Tipos de Estágio
    </x-sidebar-nav-link>

    <x-sidebar-nav-link route="" icon="exclamation-octagon">
      Gerenciar Exceções de CNPJ
    </x-sidebar-nav-link>
  @endcan

</ul>
