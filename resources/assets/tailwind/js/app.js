import CliConsole from './cli-console';
window.CliConsole = CliConsole;

const cliConsoleEvent = new Event("cli-console-ready");
document.dispatchEvent(cliConsoleEvent);
