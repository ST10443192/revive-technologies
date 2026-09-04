</main>
<footer>
    &copy; <?php echo date('Y'); ?> Bee's Events & Decor &middot; Event Decorating &amp; Freelance Management System
</footer>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const targets = document.querySelectorAll('.reveal, .reveal-fade');
    if (!('IntersectionObserver' in window) || !targets.length) {
        targets.forEach(el => el.classList.add('in-view'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    targets.forEach(el => io.observe(el));
});
</script>
</body>
</html>
