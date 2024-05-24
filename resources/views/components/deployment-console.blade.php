<div class="row">
    <div class="col-12 alert-warning">
        <div class="overflow-auto console deployment-console"></div>
    </div>
</div>
<script>
    document.addEventListener('alpine:init', () => {
        console.log('module website-base console init ...')

        let c = new CliConsole("deployment-console");
        c.start();
    })
</script>
