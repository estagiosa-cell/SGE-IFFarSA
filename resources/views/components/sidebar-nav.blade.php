<ul class="nav nav-pills flex-column">
    @can('is-direcao-ensino')
        <x-sidebar-nav-link route="teaching-director.index" icon="mortarboard">
            Consulta de Estágios
        </x-sidebar-nav-link>
    @elsecan('view-internships')
        <x-sidebar-nav-link route="internship-view.index" icon="briefcase">
            Estágios
        </x-sidebar-nav-link>
    @endcan

    @can('is-admin')
        <x-sidebar-nav-link route="admin.dashboard" icon="columns-gap">
            Dashboard
        </x-sidebar-nav-link>

        <x-sidebar-nav-link route="admin.reports.index" icon="graph-up">
            Relatórios Gerenciais
        </x-sidebar-nav-link>

        <x-sidebar-nav-link route="admin.internships.index" icon="briefcase">
            Estágios
        </x-sidebar-nav-link>

        <x-sidebar-nav-link route="admin.supervisor-evaluations.index" icon="clipboard-check">
            Avaliações do Supervisor
        </x-sidebar-nav-link>

        <x-sidebar-nav-link route="admin.email-logs.index" icon="envelope-paper">
            Logs de E-mails
        </x-sidebar-nav-link>
    @endcan

    @cannot('is-direcao-ensino')
        <x-sidebar-nav-link route="reports.internships" icon="filetype-csv">
            Exportar Dados de Estágios
        </x-sidebar-nav-link>
    @endcannot

    @can('is-admin')
        <li class="nav-item">
            <hr>
            <p class="text-muted small fw-bold text-uppercase mb-1">Configurações</p>
        </li>

        <x-sidebar-nav-link route="admin.users.index" icon="people">
            Gerenciar Usuários
        </x-sidebar-nav-link>

        <x-sidebar-nav-link route="admin.courses.index" icon="book">
            Gerenciar Cursos
        </x-sidebar-nav-link>

        <x-sidebar-nav-link route="admin.internship-types.index" icon="tags">
            Gerenciar Tipos de Estágio
        </x-sidebar-nav-link>

        <x-sidebar-nav-link route="admin.companies.index" icon="building-gear">
            Gerenciar Partes concedentes
        </x-sidebar-nav-link>

        <x-sidebar-nav-link route="admin.backup" icon="database-down">
            Backup da Base de Dados
        </x-sidebar-nav-link>
    @endcan

</ul>
