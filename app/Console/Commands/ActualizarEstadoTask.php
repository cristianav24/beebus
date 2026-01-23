<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Attendance;

class ActualizarEstadoTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar base de datos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*$text = "[" . date("Y-m-d H:i:s") . "] : Hola que hace";
        Storage::append("archivo.txt", $text);*/
    
        $status = DB::table('attendances')->where('status', '=', '1')->get();

        foreach($status as $sta)
        {
            if($sta->transcurso < 30)
            {
                Attendance::where('id', $sta->id)->update(['transcurso' => (intval($sta->transcurso) + 1)]);
                Storage::append("archivo.txt", $sta->transcurso);
            }
            else{
                Attendance::where('id', $sta->id)->update(['status' => 0, 'transcurso' => 0]);
            }

        }
    }
}
