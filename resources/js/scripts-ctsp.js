// Hiệu ứng 3D khi di chuột vào card ở giữa
document.addEventListener('mousemove', function (e) {
    const centerCard = document.querySelector('.card.center');
    if (!centerCard) return;

    const rect = centerCard.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    if (x >= 0 && x <= rect.width && y >= 0 && y <= rect.height) {
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateY = (x - centerX) / 20;
        const rotateX = (centerY - y) / 20;
        centerCard.style.transform = `translateZ(10px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
    } else {
        centerCard.style.transform = '';
    }
});

// Reset transform khi chuột rời khỏi màn hình
document.addEventListener('mouseleave', function () {
    const centerCard = document.querySelector('.card.center');
    if (centerCard) {
        centerCard.style.transform = '';
    }
});

