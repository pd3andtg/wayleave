@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')

  <div class="row">

    {{-- Card 1: Chart with filter --}}
    <div class="col-md-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <p class="card-title">Card Title Here</p>
          <p class="text-muted font-weight-light">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
          {{-- Replace with your chart legend --}}
          <div id="card-1-legend" class="chartjs-legend mt-4 mb-2"></div>
          {{-- Replace with your chart --}}
          <canvas id="card-1-chart"></canvas>
        </div>
        <div class="card border-right-0 border-left-0 border-bottom-0">
          <div class="d-flex justify-content-center justify-content-md-end">
            <div class="dropdown flex-md-grow-1 flex-xl-grow-0">
              <button class="btn btn-lg btn-outline-light dropdown-toggle rounded-0 border-top-0 border-bottom-0" type="button" data-bs-toggle="dropdown">
                Filter
              </button>
              <div class="dropdown-menu dropdown-menu-right">
                {{-- Replace options with your own filter values --}}
                <a class="dropdown-item" href="#">Option One</a>
                <a class="dropdown-item" href="#">Option Two</a>
                <a class="dropdown-item" href="#">Option Three</a>
                <a class="dropdown-item" href="#">Option Four</a>
              </div>
            </div>
            <button class="btn btn-lg btn-outline-light text-primary rounded-0 border-0 d-none d-md-block" type="button">View all</button>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 2: Stats + chart --}}
    <div class="col-md-6 grid-margin stretch-card">
      <div class="card border-bottom-0">
        <div class="card-body pb-0">
          <p class="card-title">Card Title Here</p>
          <p class="text-muted font-weight-light">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>
          <div class="d-flex flex-wrap mb-5">
            {{-- Replace Stat Labels and 000 values with real data from your controller --}}
            <div class="me-5 mt-3">
              <p class="text-muted">Stat Label</p>
              <h3>000</h3>
            </div>
            <div class="me-5 mt-3">
              <p class="text-muted">Stat Label</p>
              <h3>000</h3>
            </div>
            <div class="me-5 mt-3">
              <p class="text-muted">Stat Label</p>
              <h3>000</h3>
            </div>
            <div class="mt-3">
              <p class="text-muted">Stat Label</p>
              <h3>000</h3>
            </div>
          </div>
        </div>
        {{-- Replace with your chart --}}
        <canvas id="card-2-chart" class="w-100"></canvas>
      </div>
    </div>

  </div>

  {{-- Card 3: Detailed Reports --}}
  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card position-relative">
        <div class="card-body">
          <p class="card-title">Detailed Reports</p>
          <div class="row">
            <div class="col-md-12 col-xl-3 d-flex flex-column justify-content-center">
              <div class="ml-xl-4">
                {{-- Replace 00000 and Section Label with real data --}}
                <h1>00000</h1>
                <h3 class="font-weight-light mb-xl-4">Section Label</h3>
                <p class="text-muted mb-2 mb-xl-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua ut enim.</p>
              </div>
            </div>
            <div class="col-md-12 col-xl-9">
              <div class="row">
                <div class="col-md-6 mt-3 col-xl-5">
                  {{-- Replace with your donut/pie chart --}}
                  <canvas id="card-3-chart"></canvas>
                  <div id="card-3-legend"></div>
                </div>
                <div class="col-md-6 col-xl-7">
                  <div class="table-responsive mb-3 mb-md-0">
                    <table class="table table-borderless report-table">
                      {{-- Replace Row Label and 000 values with real data from your controller --}}
                      <tr>
                        <td class="text-muted">Row Label</td>
                        <td class="w-100 px-0">
                          <div class="progress progress-md mx-4">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </td>
                        <td><h5 class="font-weight-bold mb-0">000</h5></td>
                      </tr>
                      <tr>
                        <td class="text-muted">Row Label</td>
                        <td class="w-100 px-0">
                          <div class="progress progress-md mx-4">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </td>
                        <td><h5 class="font-weight-bold mb-0">000</h5></td>
                      </tr>
                      <tr>
                        <td class="text-muted">Row Label</td>
                        <td class="w-100 px-0">
                          <div class="progress progress-md mx-4">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </td>
                        <td><h5 class="font-weight-bold mb-0">000</h5></td>
                      </tr>
                      <tr>
                        <td class="text-muted">Row Label</td>
                        <td class="w-100 px-0">
                          <div class="progress progress-md mx-4">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </td>
                        <td><h5 class="font-weight-bold mb-0">000</h5></td>
                      </tr>
                      <tr>
                        <td class="text-muted">Row Label</td>
                        <td class="w-100 px-0">
                          <div class="progress progress-md mx-4">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 40%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </td>
                        <td><h5 class="font-weight-bold mb-0">000</h5></td>
                      </tr>
                      <tr>
                        <td class="text-muted">Row Label</td>
                        <td class="w-100 px-0">
                          <div class="progress progress-md mx-4">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </td>
                        <td><h5 class="font-weight-bold mb-0">000</h5></td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection