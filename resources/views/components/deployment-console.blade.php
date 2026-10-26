<div class="grid grid-cols-2 gap-4 ">
    <div class="col-span-2 alert-warning">
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
