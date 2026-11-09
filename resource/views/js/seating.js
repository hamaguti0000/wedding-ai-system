let dragged = null;
let updates = [];

document.addEventListener("dragstart", e => {
  if (e.target.draggable) {
    dragged = e.target;
  }
});

document.addEventListener("dragover", e => {
  e.preventDefault();
});

document.addEventListener("drop", e => {
  e.preventDefault();

  if (!dragged) return;

  if (e.target.classList.contains("seat")) {

    const fromUserId = dragged.dataset.userId;
    const toUserId = e.target.dataset.userId;

    const fromSeat = dragged;
    const toSeat = e.target;

    // 中身交換
    const tempHTML = fromSeat.innerHTML;
    const tempUserId = fromSeat.dataset.userId;

    fromSeat.innerHTML = toSeat.innerHTML;
    fromSeat.dataset.userId = toSeat.dataset.userId;

    toSeat.innerHTML = tempHTML;
    toSeat.dataset.userId = tempUserId;

    const tableId = fromSeat.closest(".table").dataset.tableId;

    updates.push({
      user_id: fromSeat.dataset.userId,
      table_id: tableId,
      seat_number: fromSeat.dataset.seatNumber
    });

    updates.push({
      user_id: toSeat.dataset.userId,
      table_id: tableId,
      seat_number: toSeat.dataset.seatNumber
    });
  }
});

document.getElementById("save-button").addEventListener("click", () => {

  fetch("seating.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ updates })
  })
    .then(res => res.json())
    .then(data => {
      alert("保存しました");
      updates = [];
    });
});
