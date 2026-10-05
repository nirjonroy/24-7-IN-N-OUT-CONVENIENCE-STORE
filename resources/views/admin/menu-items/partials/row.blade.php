<tr>
  <td>{!! $depth ? '&mdash; ' : '' !!}{{ $item->label }}</td>
  <td>{{ ucfirst($item->link_type) }}</td>
  <td>{{ $item->link_type === 'page' ? ($item->page?->name ?: 'Page removed') : $item->url }}</td>
  <td>{{ $item->target }}</td>
  <td><span class="badge {{ $item->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span></td>
  <td>{{ $item->sort_order }}</td>
  <td><a href="{{ route('admin.menus.items.edit', [$menu, $item]) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a> <form action="{{ route('admin.menus.items.destroy', [$menu, $item]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this menu item?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button></form></td>
</tr>
