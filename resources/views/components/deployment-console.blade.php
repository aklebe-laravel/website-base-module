<div class="row">
    <div class="col-12 alert-warning">
        <div class="overflow-auto console deployment-console"></div>
    </div>
</div>
<script>
    // document.addEventListener('alpine:init', () => {
    // window.addEventListener('load', () => {
    document.addEventListener('cli-console-ready', () => {
        console.log('event "cli-console-ready" ready ...')

        let c = new CliConsole("deployment-console");
        c.start();
    })
</script>
