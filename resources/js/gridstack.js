import { GridStack } from 'gridstack';
import 'gridstack/dist/gridstack.min.css';

document.addEventListener('DOMContentLoaded', () => {
  const grid = GridStack.init({
    // You can customize options here
    float: true,
    animate: true,
    cellHeight: 'auto',
  });

  // Listen for change events (drag/resize) and save layout
  grid.on('change', function(event, items) {
    const layout = grid.save();
    fetch('/dashboard/save-layout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ layout })
    })
    .then(response => response.json())
    .then(data => {
      console.log('Layout saved', data);
    })
    .catch(err => console.error('Error saving layout', err));
  });
});
