use Illuminate\Support\Facades\Schedule;

Schedule::command('clean:trash')->daily();