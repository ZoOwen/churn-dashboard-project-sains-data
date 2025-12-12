<div class="table-responsive" style="max-height:600px; overflow:auto;">
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                @foreach ($selectedColumns as $col)
                    <th data-col="{{ $col }}" style="position: sticky; top: 0; background: #f8f9fa; z-index: 2;">
                        {{ $col }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($paginator as $row)
                <tr>
                    @foreach ($selectedColumns as $col)
                        <td data-col="{{ $col }}">{{ $row[$col] }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-3">
        {{ $paginator->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
</div>
